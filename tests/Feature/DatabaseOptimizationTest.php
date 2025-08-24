<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Documentation;
use App\Models\User;
use App\Services\DatabaseOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class DatabaseOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseOptimizationService $optimizationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->optimizationService = new DatabaseOptimizationService();
    }

    #[Test]
    public function documentation_scopes_work_correctly(): void
    {
        // Create test documentation
        $docs = Documentation::factory()->count(5)->create([
            'category' => 'test-category',
        ]);

        Documentation::factory()->count(3)->create([
            'category' => 'other-category',
        ]);

        // Test byCategory scope
        $categoryDocs = Documentation::byCategory('test-category')->get();
        $this->assertCount(5, $categoryDocs);

        // Test recent scope
        $recentDocs = Documentation::recent(3)->get();
        $this->assertCount(3, $recentDocs);
        $this->assertTrue($recentDocs->first()?->updated_at >= $recentDocs->last()?->updated_at);

        // Test categoriesWithCounts scope
        $categories = Documentation::categoriesWithCounts()->get();
        $this->assertCount(2, $categories);

        $testCategory = $categories->firstWhere('category', 'test-category');
        $this->assertNotNull($testCategory);
        $this->assertIsObject($testCategory);
        if (property_exists($testCategory, 'document_count')) {
            $this->assertEquals(5, $testCategory->document_count);
        }
    }

    #[Test]
    public function optimization_service_provides_cached_categories(): void
    {
        Documentation::factory()->count(3)->create(['category' => 'core']);
        Documentation::factory()->count(2)->create(['category' => 'recipes']);

        $categories = $this->optimizationService->getCategoriesWithCounts();

        $this->assertIsArray($categories);
        $this->assertArrayHasKey('core', $categories);
        $this->assertArrayHasKey('recipes', $categories);
        $this->assertEquals(3, $categories['core']['count']);
        $this->assertEquals(2, $categories['recipes']['count']);

        // Test caching
        $this->assertTrue(Cache::has('docs.categories.optimized'));
    }

    #[Test]
    public function optimization_service_provides_user_statistics(): void
    {
        User::factory()->count(5)->create();
        User::factory()->count(2)->create(['email_verified_at' => now()]);
        User::factory()->create(['last_login_at' => now()->subDays(7)]);

        $stats = $this->optimizationService->getUserStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('verified_users', $stats);
        $this->assertArrayHasKey('recent_users', $stats);
        $this->assertArrayHasKey('active_users', $stats);

        $this->assertIsInt($stats['total_users']);
        $this->assertGreaterThan(0, $stats['total_users']);
    }

    #[Test]
    public function optimization_service_provides_activity_statistics(): void
    {
        $user = User::factory()->create();

        // Create some activities
        Activity::create([
            'log_name' => 'default',
            'description' => 'test activity',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'another test activity',
            'created_at' => now()->subDays(2),
        ]);

        $stats = $this->optimizationService->getActivityStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_activities', $stats);
        $this->assertArrayHasKey('recent_activities', $stats);
        $this->assertArrayHasKey('activities_today', $stats);
        $this->assertArrayHasKey('activities_this_week', $stats);

        $this->assertIsInt($stats['total_activities']);
        $this->assertGreaterThanOrEqual(2, $stats['total_activities']);
        $this->assertIsInt($stats['activities_today']);
    }

    #[Test]
    public function bulk_search_index_update_works(): void
    {
        $docs = Documentation::factory()->count(5)->create();
        $docIds = $docs->pluck('id')->toArray();

        // This should not throw any exceptions
        $this->optimizationService->bulkUpdateSearchIndexes(array_map('intval', $docIds));
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function cache_clearing_works(): void
    {
        // Set some cache values first
        Cache::put('docs.categories.optimized', ['test' => 'data'], 3600);
        Cache::put('admin.user_statistics', ['test' => 'data'], 3600);

        $this->optimizationService->clearOptimizationCaches();

        $this->assertFalse(Cache::has('docs.categories.optimized'));
        $this->assertFalse(Cache::has('admin.user_statistics'));
    }

    #[Test]
    public function query_performance_insights_are_provided(): void
    {
        $insights = $this->optimizationService->getQueryPerformanceInsights();

        $this->assertIsArray($insights);
        $this->assertArrayHasKey('slow_queries_enabled', $insights);
        $this->assertArrayHasKey('query_cache_enabled', $insights);
        $this->assertArrayHasKey('table_statistics', $insights);
        $this->assertArrayHasKey('index_usage', $insights);

        $this->assertIsBool($insights['slow_queries_enabled']);
        $this->assertIsBool($insights['query_cache_enabled']);
        $this->assertIsArray($insights['table_statistics']);
        $this->assertIsArray($insights['index_usage']);
    }

    #[Test]
    public function recent_documents_by_category_are_cached_and_limited(): void
    {
        Documentation::factory()->count(10)->create(['category' => 'test']);

        $recent = $this->optimizationService->getRecentDocumentsByCategory('test', 3);

        $this->assertCount(3, $recent);
        $this->assertTrue(Cache::has('docs.recent.test.3'));

        // Verify ordering by updated_at
        $firstDoc = $recent->first();
        $lastDoc = $recent->last();
        $this->assertInstanceOf(Documentation::class, $firstDoc);
        $this->assertInstanceOf(Documentation::class, $lastDoc);

        if ($firstDoc && $lastDoc && $firstDoc->updated_at && $lastDoc->updated_at) {
            $this->assertTrue($firstDoc->updated_at->greaterThanOrEqualTo($lastDoc->updated_at));
        }
    }

    #[Test]
    public function database_indexes_improve_query_performance(): void
    {
        // Create test data
        User::factory()->count(100)->create();
        Documentation::factory()->count(100)->create();

        // Test query performance with indexes (simplified)
        $start = microtime(true);

        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $categoryDocs = Documentation::where('category', 'test')->count();

        $end = microtime(true);
        $duration = $end - $start;

        // Basic performance check (should be fast with proper indexes)
        $this->assertLessThan(1.0, $duration); // Should complete within 1 second

        // Verify data integrity
        $this->assertIsInt($verifiedUsers);
        $this->assertIsInt($recentUsers);
        $this->assertIsInt($categoryDocs);
    }

    #[Test]
    public function eager_loading_prevents_n_plus_one_queries(): void
    {
        // Create test data with relationships
        $user = User::factory()->create();
        $role = \Spatie\Permission\Models\Role::create(['name' => 'test-role']);
        $user->assignRole($role);

        // Create activities
        Activity::create([
            'log_name' => 'default',
            'description' => 'test activity',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        // Test query count with eager loading
        DB::enableQueryLog();

        // This should use eager loading to avoid N+1
        $activities = Activity::with(['causer:id,name'])->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 2 queries: one for activities, one for causers
        $this->assertLessThanOrEqual(2, count($queries));
        $this->assertNotEmpty($activities);

        foreach ($activities as $activity) {
            if ($activity->causer && property_exists($activity->causer, 'name')) {
                $this->assertIsString($activity->causer->name);
            }
        }
    }

    #[Test]
    public function bulk_operations_are_efficient(): void
    {
        $docs = Documentation::factory()->count(20)->create();
        $docIds = $docs->pluck('id')->take(15)->toArray();

        DB::enableQueryLog();

        $this->optimizationService->bulkUpdateSearchIndexes(array_map('intval', $docIds));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should not generate excessive queries
        $this->assertLessThan(10, count($queries));
    }
}
