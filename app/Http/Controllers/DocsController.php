<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DocsController extends Controller
{
    /**
     * Display the documentation index page with all categories.
     */
    public function index(): View
    {
        $categories = Cache::remember('docs.categories', 3600, function () {
            return Documentation::select('category')
                ->selectRaw('COUNT(*) as document_count')
                ->groupBy('category')
                ->orderBy('category')
                ->get()
                ->mapWithKeys(function ($item) {
                    $category = $item->category ?? 'general';
                    $count = 0;
                    if (isset($item->document_count) && is_numeric($item->document_count)) {
                        $count = (int) $item->document_count;
                    }

                    return [
                        $category => [
                            'name' => $this->getCategoryDisplayName($category),
                            'count' => $count,
                            'documents' => $this->getRecentDocumentsInCategory($category),
                        ],
                    ];
                });
        });

        return view('docs.index', compact('categories'));
    }

    /**
     * Display documents in a specific category.
     */
    public function category(string $category): View
    {
        $documents = Cache::remember("docs.category.{$category}", 3600, function () use ($category) {
            return Documentation::byCategory($category)
                ->orderBy('title')
                ->get();
        });

        if ($documents instanceof \Illuminate\Database\Eloquent\Collection && $documents->isEmpty()) {
            abort(404, "Category '{$category}' not found");
        }

        $categoryName = $this->getCategoryDisplayName($category);

        return view('docs.category', compact('documents', 'category', 'categoryName'));
    }

    /**
     * Display a specific documentation page.
     */
    public function show(string $category, string $slug): View
    {
        $document = Cache::remember("docs.{$category}.{$slug}", 3600, function () use ($category, $slug) {
            return Documentation::where('category', $category)
                ->where('slug', $slug)
                ->first();
        });

        if (!$document instanceof Documentation) {
            abort(404, 'Documentation page not found');
        }

        // Get navigation items for sidebar
        $navigation = Cache::remember("docs.navigation.{$category}", 3600, function () use ($category) {
            return Documentation::byCategory($category)
                ->orderBy('title')
                ->select('title', 'slug', 'category')
                ->get();
        });

        // Get breadcrumbs
        $breadcrumbs = $this->buildBreadcrumbs($category, $document);

        return view('docs.show', compact('document', 'navigation', 'breadcrumbs'));
    }

    /**
     * Search documentation (API endpoint).
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3|max:100',
            'category' => 'sometimes|string|in:core,recipes,deployment,troubleshooting,general',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $queryInput = $request->input('q');
        $query = is_string($queryInput) ? $queryInput : '';
        $category = $request->input('category');
        $limitInput = $request->input('limit', 10);
        $limit = is_numeric($limitInput) ? (int) $limitInput : 10;

        $searchQuery = Documentation::search($query)->take($limit);

        if ($category) {
            $searchQuery->where('category', $category);
        }

        $results = $searchQuery->get()->map(function ($doc) use ($query) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'excerpt' => $doc->excerpt,
                'category' => $doc->category,
                'url' => route('docs.show', [
                    'category' => $doc->category,
                    'slug' => $doc->slug,
                ]),
                'highlighted_title' => $this->highlightSearchTerms($doc->title, $query),
                'highlighted_excerpt' => $this->highlightSearchTerms($doc->excerpt, $query),
            ];
        });

        return response()->json([
            'query' => $query,
            'results' => $results,
            'total' => $results->count(),
            'category' => $category,
        ]);
    }

    /**
     * Get display name for category.
     */
    private function getCategoryDisplayName(string $category): string
    {
        $categoryNames = [
            'core' => 'Core Komponensek',
            'recipes' => 'Receptek',
            'deployment' => 'Telepítés',
            'troubleshooting' => 'Hibaelhárítás',
            'general' => 'Általános',
        ];

        return $categoryNames[$category] ?? ucfirst($category);
    }

    /**
     * Get recent documents in category for preview.
     */
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Documentation>
     */
    private function getRecentDocumentsInCategory(string $category, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Documentation::byCategory($category)
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->select('title', 'slug', 'excerpt')
            ->get();
    }

    /**
     * Build breadcrumb navigation.
     */
    /**
     * @return array<int, array<string, string|null>>
     */
    private function buildBreadcrumbs(string $category, Documentation $document): array
    {
        return [
            [
                'title' => 'Dokumentáció',
                'url' => route('docs.index'),
            ],
            [
                'title' => $this->getCategoryDisplayName($category),
                'url' => route('docs.category', $category),
            ],
            [
                'title' => $document->title,
                'url' => null, // Current page
            ],
        ];
    }

    /**
     * Highlight search terms in text.
     */
    private function highlightSearchTerms(string $text, string $query): string
    {
        $searchTerms = explode(' ', $query);

        foreach ($searchTerms as $term) {
            if (strlen(trim($term)) >= 2) {
                $result = preg_replace(
                    '/(' . preg_quote($term, '/') . ')/i',
                    '<mark class="bg-yellow-200 dark:bg-yellow-600 px-1 rounded">$1</mark>',
                    $text,
                );
                if ($result !== null) {
                    $text = $result;
                }
            }
        }

        return $text;
    }
}
