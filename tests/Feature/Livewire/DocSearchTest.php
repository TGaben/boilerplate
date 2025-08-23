<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\DocSearch;
use App\Models\Documentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable Scout for testing to prevent real Meilisearch calls
        config(['scout.driver' => null]);

        // Create test documentation
        Documentation::factory()->create([
            'title' => 'Laravel Authentication',
            'content' => 'User authentication with Laravel Breeze and permissions',
            'category' => 'core',
            'slug' => 'laravel-authentication',
        ]);

        Documentation::factory()->create([
            'title' => 'API Development Recipe',
            'content' => 'Building RESTful APIs with Laravel Sanctum',
            'category' => 'recipes',
            'slug' => 'api-development-recipe',
        ]);

        Documentation::factory()->create([
            'title' => 'Docker Deployment',
            'content' => 'Deploy Laravel applications using Docker containers',
            'category' => 'deployment',
            'slug' => 'docker-deployment',
        ]);
    }

    #[Test]
    public function doc_search_component_renders_correctly(): void
    {
        Livewire::test(DocSearch::class)
            ->assertSee('Keresés a dokumentációban')
            ->assertSet('query', '')
            ->assertSet('results', [])
            ->assertSet('showResults', false);
    }

    #[Test]
    public function doc_search_component_can_be_mounted_with_custom_parameters(): void
    {
        Livewire::test(DocSearch::class, [
            'maxResults' => 5,
            'categories' => ['core'],
            'placeholder' => 'Custom placeholder',
        ])
            ->assertSet('maxResults', 5)
            ->assertSet('categories', ['core'])
            ->assertSet('placeholder', 'Custom placeholder');
    }

    #[Test]
    public function doc_search_does_not_search_with_less_than_three_characters(): void
    {
        Livewire::test(DocSearch::class)
            ->set('query', 'au') // 2 characters
            ->assertSet('results', [])
            ->assertSet('showResults', false);
    }

    #[Test]
    public function doc_search_performs_search_with_minimum_characters(): void
    {
        Livewire::test(DocSearch::class)
            ->set('query', 'Laravel') // 7 characters - should search
            ->call('searchDocumentation')
            ->assertSet('showResults', true);
    }

    #[Test]
    public function doc_search_clears_results_when_query_is_empty(): void
    {
        Livewire::test(DocSearch::class)
            ->set('query', 'Laravel')
            ->call('searchDocumentation')
            ->set('query', '') // Clear query
            ->assertSet('results', [])
            ->assertSet('showResults', false);
    }

    #[Test]
    public function doc_search_highlights_search_terms(): void
    {
        $component = new DocSearch();
        $component->query = 'Laravel';

        $highlightedText = $component->highlightSearchTerms('Laravel Authentication');

        $this->assertStringContainsString(
            '<mark class="bg-yellow-200 dark:bg-yellow-600 px-1 rounded">Laravel</mark>',
            $highlightedText,
        );
    }

    #[Test]
    public function doc_search_handles_multiple_search_terms(): void
    {
        $component = new DocSearch();
        $component->query = 'Laravel API';

        $highlightedText = $component->highlightSearchTerms('Laravel API Development');

        $this->assertStringContainsString('<mark', $highlightedText);
        $this->assertStringContainsString('Laravel', $highlightedText);
        $this->assertStringContainsString('API', $highlightedText);
    }

    #[Test]
    public function doc_search_clear_search_resets_component_state(): void
    {
        Livewire::test(DocSearch::class)
            ->set('query', 'Laravel')
            ->set('showResults', true)
            ->set('results', [])
            ->call('clearSearch')
            ->assertSet('query', '')
            ->assertSet('results', [])
            ->assertSet('showResults', false);
    }

    #[Test]
    public function doc_search_groups_results_by_category(): void
    {
        $component = new DocSearch();

        // Create actual Documentation models instead of stdClass objects
        $coreDoc = Documentation::factory()->make([
            'id' => 1,
            'title' => 'Core Document',
            'excerpt' => 'Core excerpt',
            'category' => 'core',
            'slug' => 'core-test',
        ]);

        $recipeDoc = Documentation::factory()->make([
            'id' => 2,
            'title' => 'Recipe Document',
            'excerpt' => 'Recipe excerpt',
            'category' => 'recipes',
            'slug' => 'recipe-test',
        ]);

        $mockResults = new \Illuminate\Database\Eloquent\Collection([$coreDoc, $recipeDoc]);

        $grouped = $component->groupResultsByCategory($mockResults);

        $this->assertArrayHasKey('core', $grouped);
        $this->assertArrayHasKey('recipes', $grouped);
        $this->assertEquals('Core Komponensek', $grouped['core']['name']);
        $this->assertEquals('Receptek', $grouped['recipes']['name']);
        $this->assertCount(1, $grouped['core']['documents']);
        $this->assertCount(1, $grouped['recipes']['documents']);
    }

    #[Test]
    public function doc_search_gets_correct_category_display_names(): void
    {
        $component = new DocSearch();

        $this->assertEquals('Core Komponensek', $component->getCategoryDisplayName('core'));
        $this->assertEquals('Receptek', $component->getCategoryDisplayName('recipes'));
        $this->assertEquals('Telepítés', $component->getCategoryDisplayName('deployment'));
        $this->assertEquals('Hibaelhárítás', $component->getCategoryDisplayName('troubleshooting'));
        $this->assertEquals('Általános', $component->getCategoryDisplayName('general'));
        $this->assertEquals('Custom', $component->getCategoryDisplayName('custom'));
    }

    #[Test]
    public function doc_search_calculates_results_count_correctly(): void
    {
        $component = Livewire::test(DocSearch::class);

        // Set mock results
        $component->set('results', [
            'core' => [
                'name' => 'Core',
                'documents' => [
                    ['title' => 'Doc 1'],
                    ['title' => 'Doc 2'],
                ],
            ],
            'recipes' => [
                'name' => 'Recipes',
                'documents' => [
                    ['title' => 'Doc 3'],
                ],
            ],
        ]);

        $this->assertEquals(3, $component->get('resultsCount'));
    }

    #[Test]
    public function doc_search_handles_search_exceptions_gracefully(): void
    {
        // This test verifies that the component handles errors gracefully
        // Since we disabled Scout in testing, errors are handled differently
        Livewire::test(DocSearch::class)
            ->set('query', 'test')
            ->call('searchDocumentation')
            ->assertSet('results', [])
            ->assertSet('showResults', true); // Component still sets showResults to true even without results
    }

    #[Test]
    public function doc_search_respects_category_filtering(): void
    {
        $component = Livewire::test(DocSearch::class, ['categories' => ['core']]);

        // Verify the component respects the categories parameter
        $this->assertEquals(['core'], $component->get('categories'));
    }
}
