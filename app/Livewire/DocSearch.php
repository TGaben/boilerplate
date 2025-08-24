<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Documentation;
use Livewire\Component;

class DocSearch extends Component
{
    public string $query = '';

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $results = [];

    public bool $showResults = false;

    public int $maxResults = 10;

    /**
     * @var array<string>
     */
    public array $categories = [];

    public string $placeholder = 'Keresés a dokumentációban...';

    /**
     * @var array<string, string>
     */
    protected $listeners = [
        'resetSearch' => 'clearSearch',
    ];

    /**
     * @param array<string> $categories
     */
    public function mount(
        int $maxResults = 10,
        array $categories = [],
        string $placeholder = 'Keresés a dokumentációban...',
    ): void {
        $this->maxResults = $maxResults;
        $this->categories = $categories;
        $this->placeholder = $placeholder;
    }

    public function updatedQuery(): void
    {
        $this->searchDocumentation();
    }

    public function searchDocumentation(): void
    {
        // Minimum 3 karakter után keresés
        if (strlen(trim($this->query)) < 3) {
            $this->results = [];
            $this->showResults = false;

            return;
        }

        try {
            $searchQuery = Documentation::search($this->query)
                ->take($this->maxResults);

            // Kategória szűrés ha megadva
            if (!empty($this->categories)) {
                $searchQuery->whereIn('category', $this->categories);
            }

            $searchResults = $searchQuery->get();

            // Eredmények kategóriák szerint csoportosítva
            $this->results = $this->groupResultsByCategory($searchResults);
            $this->showResults = true;

        } catch (\Exception $e) {
            $this->results = [];
            $this->showResults = false;

            // Log error for debugging
            logger()->error('DocSearch error: ' . $e->getMessage());
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Documentation> $searchResults
     *
     * @return array<string, array<string, mixed>>
     */
    public function groupResultsByCategory($searchResults): array
    {
        $grouped = [];

        foreach ($searchResults as $result) {
            $category = $result->category ?? 'general';

            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'name' => $this->getCategoryDisplayName($category),
                    'documents' => [],
                ];
            }

            $grouped[$category]['documents'][] = [
                'id' => $result->id,
                'title' => $result->title ?? '',
                'excerpt' => $result->excerpt ?? '',
                'url' => $result->url ?? '',
                'category' => $category,
                'highlighted_title' => $this->highlightSearchTerms($result->title ?? ''),
                'highlighted_excerpt' => $this->highlightSearchTerms($result->excerpt ?? ''),
            ];
        }

        return $grouped;
    }

    public function getCategoryDisplayName(string $category): string
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

    public function highlightSearchTerms(string $text): string
    {
        if (empty($this->query)) {
            return e($text); // Escape if no highlighting needed
        }

        // First escape the input text to prevent XSS
        $escapedText = e($text);
        $searchTerms = explode(' ', $this->query);

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

    public function clearSearch(): void
    {
        $this->query = '';
        $this->results = [];
        $this->showResults = false;
    }

    public function selectResult(string $url): void
    {
        $highlightParam = $this->query ? '?highlight=' . urlencode($this->query) : '';
        $this->redirect($url . $highlightParam);
    }

    public function getResultsCountProperty(): int
    {
        $count = 0;
        foreach ($this->results as $category) {
            if (isset($category['documents']) && is_array($category['documents'])) {
                $count += count($category['documents']);
            }
        }

        return $count;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.doc-search');
    }
}
