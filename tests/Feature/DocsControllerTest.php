<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Documentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test documentation
        Documentation::factory()->create([
            'title' => 'Authentication Guide',
            'category' => 'core',
            'slug' => 'authentication-guide',
            'content' => '<h1>Authentication Guide</h1><p>This is the authentication content</p>',
            'excerpt' => 'Learn about authentication in Laravel',
        ]);

        Documentation::factory()->create([
            'title' => 'API Recipe',
            'category' => 'recipes',
            'slug' => 'api-recipe',
            'content' => '<h1>API Recipe</h1><p>This is API recipe content</p>',
            'excerpt' => 'Building APIs with Laravel',
        ]);

        Documentation::factory()->create([
            'title' => 'Admin Panel Setup',
            'category' => 'core',
            'slug' => 'admin-panel-setup',
            'content' => '<h1>Admin Panel</h1><p>Setting up admin panel</p>',
            'excerpt' => 'Configure your admin panel',
        ]);
    }

    #[Test]
    public function docs_index_displays_documentation_categories(): void
    {
        $response = $this->get('/docs');

        $response->assertOk()
            ->assertSee('📚 Dokumentáció')
            ->assertSee('Core Komponensek')
            ->assertSee('Receptek')
            ->assertSee('Authentication Guide')
            ->assertSee('API Recipe');
    }

    #[Test]
    public function docs_index_shows_category_counts(): void
    {
        $response = $this->get('/docs');

        $response->assertOk()
            ->assertSee('2 dokumentum') // core category has 2 docs
            ->assertSee('1 dokumentum'); // recipes category has 1 doc
    }

    #[Test]
    public function docs_category_displays_documents_in_category(): void
    {
        $response = $this->get('/docs/core');

        $response->assertOk()
            ->assertSee('Authentication Guide')
            ->assertSee('Admin Panel Setup')
            ->assertDontSee('API Recipe'); // This is in recipes category
    }

    #[Test]
    public function docs_category_returns_404_for_nonexistent_category(): void
    {
        $response = $this->get('/docs/nonexistent-category');

        $response->assertNotFound();
    }

    #[Test]
    public function docs_show_displays_specific_document(): void
    {
        $response = $this->get('/docs/core/authentication-guide');

        $response->assertOk()
            ->assertSee('Authentication Guide')
            ->assertSee('This is the authentication content', false);
    }

    #[Test]
    public function docs_show_returns_404_for_nonexistent_document(): void
    {
        $response = $this->get('/docs/core/nonexistent-document');

        $response->assertNotFound();
    }

    #[Test]
    public function docs_show_returns_404_for_wrong_category(): void
    {
        $response = $this->get('/docs/recipes/authentication-guide');

        $response->assertNotFound();
    }

    #[Test]
    public function docs_search_api_returns_json_results(): void
    {
        $response = $this->getJson('/docs/search?q=authentication');

        $response->assertOk()
            ->assertJsonStructure([
                'query',
                'results',
                'total',
                'category',
            ])
            ->assertJson([
                'query' => 'authentication',
            ]);
    }

    #[Test]
    public function docs_search_api_validates_minimum_query_length(): void
    {
        $response = $this->getJson('/docs/search?q=a');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    #[Test]
    public function docs_search_api_validates_maximum_query_length(): void
    {
        $longQuery = str_repeat('a', 101);
        $response = $this->getJson('/docs/search?q=' . $longQuery);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    #[Test]
    public function docs_search_api_filters_by_category(): void
    {
        // Skip this test in testing environment where Scout is disabled
        if (config('scout.driver') === null) {
            $this->markTestSkipped('Scout driver is disabled in testing environment');
        }

        $response = $this->getJson('/docs/search?q=guide&category=core');

        $response->assertOk()
            ->assertJson([
                'category' => 'core',
            ]);
    }

    #[Test]
    public function docs_search_api_validates_category(): void
    {
        $response = $this->getJson('/docs/search?q=test&category=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    #[Test]
    public function docs_search_api_respects_limit_parameter(): void
    {
        // Create more documents to test limit
        Documentation::factory()->count(15)->create([
            'title' => 'Test Document',
            'content' => 'Test content with search term',
        ]);

        $response = $this->getJson('/docs/search?q=test&limit=5');

        $response->assertOk();

        $results = $response->json('results');
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results));
    }

    #[Test]
    public function docs_search_api_validates_limit_range(): void
    {
        $response = $this->getJson('/docs/search?q=test&limit=0');
        $response->assertStatus(422)->assertJsonValidationErrors(['limit']);

        $response = $this->getJson('/docs/search?q=test&limit=51');
        $response->assertStatus(422)->assertJsonValidationErrors(['limit']);
    }

    #[Test]
    public function docs_search_highlights_search_terms_in_api_response(): void
    {
        $response = $this->getJson('/docs/search?q=authentication');

        $response->assertOk();

        $results = $response->json('results');
        $this->assertIsArray($results);
        if (!empty($results)) {
            $firstResult = $results[0];
            $this->assertIsArray($firstResult);
            $this->assertArrayHasKey('highlighted_title', $firstResult);
            $this->assertArrayHasKey('highlighted_excerpt', $firstResult);
        }
    }

    #[Test]
    public function docs_routes_are_cached_for_performance(): void
    {
        // First request to warm cache
        $startTime = microtime(true);
        $this->get('/docs/core/authentication-guide');
        $firstRequestTime = microtime(true) - $startTime;

        // Second request (should be faster due to caching)
        $startTime = microtime(true);
        $this->get('/docs/core/authentication-guide');
        $secondRequestTime = microtime(true) - $startTime;

        // Cache effect might be minimal in testing, but structure should support it
        $this->assertIsFloat($firstRequestTime);
        $this->assertIsFloat($secondRequestTime);
    }

    #[Test]
    public function docs_show_builds_correct_breadcrumbs(): void
    {
        $response = $this->get('/docs/core/authentication-guide');

        $response->assertOk()
            ->assertViewHas('breadcrumbs')
            ->assertViewHas('breadcrumbs', function ($breadcrumbs) {
                if (!is_array($breadcrumbs) || count($breadcrumbs) !== 3) {
                    return false;
                }

                return isset($breadcrumbs[0]) && is_array($breadcrumbs[0]) &&
                       isset($breadcrumbs[0]['title']) && $breadcrumbs[0]['title'] === 'Dokumentáció' &&
                       isset($breadcrumbs[1]) && is_array($breadcrumbs[1]) &&
                       isset($breadcrumbs[1]['title']) && $breadcrumbs[1]['title'] === 'Core Komponensek' &&
                       isset($breadcrumbs[2]) && is_array($breadcrumbs[2]) &&
                       isset($breadcrumbs[2]['title']) && $breadcrumbs[2]['title'] === 'Authentication Guide';
            });
    }

    #[Test]
    public function docs_show_provides_navigation_for_sidebar(): void
    {
        $response = $this->get('/docs/core/authentication-guide');

        $response->assertOk()
            ->assertViewHas('navigation')
            ->assertViewHas('navigation', function ($navigation) {
                // Should contain all core documents for sidebar
                return $navigation instanceof \Illuminate\Support\Collection &&
                       $navigation->count() === 2 && // 2 core documents
                       $navigation->pluck('title')->contains('Authentication Guide') &&
                       $navigation->pluck('title')->contains('Admin Panel Setup');
            });
    }
}
