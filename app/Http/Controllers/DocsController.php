<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DocsSearchRequest;
use App\Models\Documentation;
use App\Services\DatabaseOptimizationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function __construct(
        private readonly DatabaseOptimizationService $optimizationService,
    ) {
    }

    /**
     * Display the documentation index page with all categories.
     */
    public function index(): View
    {
        $categories = $this->optimizationService->getCategoriesWithCounts();

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
    public function search(DocsSearchRequest $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->getSearchQuery();
        $category = $request->getCategory();
        $limit = $request->getLimit();

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
     * Highlight search terms in text with XSS protection.
     */
    private function highlightSearchTerms(string $text, string $query): string
    {
        if (empty($query)) {
            return e($text); // Escape if no highlighting needed
        }

        // First escape the input text to prevent XSS
        $escapedText = e($text);
        $searchTerms = explode(' ', $query);

        foreach ($searchTerms as $term) {
            $cleanTerm = trim($term);
            if (strlen($cleanTerm) >= 2) {
                $result = preg_replace(
                    '/(' . preg_quote($cleanTerm, '/') . ')/i',
                    '<mark class="bg-yellow-200 dark:bg-yellow-600 px-1 rounded">$1</mark>',
                    $escapedText,
                );
                if ($result !== null) {
                    $escapedText = $result;
                }
            }
        }

        return $escapedText;
    }
}
