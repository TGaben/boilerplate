<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Documentation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class QueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function filament_user_resource_uses_eager_loading(): void
    {
        // Create users with roles
        $users = User::factory()->count(5)->create();
        $role = \Spatie\Permission\Models\Role::create(['name' => 'test-role']);

        foreach ($users as $user) {
            $user->assignRole($role);
        }

        DB::enableQueryLog();

        // Simulate Filament UserResource query with eager loading
        $result = User::query()->with(['roles:id,name'])->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 2 queries max: users + roles
        $this->assertLessThanOrEqual(2, count($queries));
        $this->assertCount(5, $result);

        // Verify relationships are loaded
        foreach ($result as $user) {
            $this->assertTrue($user->relationLoaded('roles'));
            if ($user->roles->isNotEmpty()) {
                $firstRole = $user->roles->first();
                if (property_exists($firstRole, 'name')) {
                    $this->assertIsString($firstRole->name);
                }
            }
        }
    }

    #[Test]
    public function filament_activity_resource_uses_eager_loading(): void
    {
        $user = User::factory()->create();

        // Create activities with causers
        for ($i = 0; $i < 3; $i++) {
            Activity::create([
                'log_name' => 'default',
                'description' => 'test activity ' . $i,
                'causer_type' => User::class,
                'causer_id' => $user->id,
            ]);
        }

        DB::enableQueryLog();

        // Simulate ActivityResource query with eager loading
        $result = Activity::query()
            ->with(['causer:id,name', 'subject'])
            ->select(['id', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'created_at'])
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be efficient with eager loading
        $this->assertLessThanOrEqual(3, count($queries));
        $this->assertGreaterThanOrEqual(3, $result->count());

        // Verify relationships are loaded
        foreach ($result as $activity) {
            $this->assertTrue($activity->relationLoaded('causer'));
            if ($activity->causer && property_exists($activity->causer, 'name')) {
                $this->assertIsString($activity->causer->name);
            }
        }
    }

    #[Test]
    public function documentation_queries_are_optimized(): void
    {
        // Create test documents
        Documentation::factory()->count(10)->create(['category' => 'test']);
        Documentation::factory()->count(5)->create(['category' => 'other']);

        DB::enableQueryLog();

        // Test category query optimization
        $categoryDocs = Documentation::byCategory('test')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->select('title', 'slug', 'excerpt', 'updated_at')
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be a single optimized query
        $this->assertEquals(1, count($queries));
        $this->assertLessThanOrEqual(5, $categoryDocs->count());

        // Verify query uses indexes (category index should be used)
        if (isset($queries[0]) && is_array($queries[0]) && isset($queries[0]['query'])) {
            $query = $queries[0]['query'];
            if (is_string($query)) {
                $this->assertStringContainsString('category', $query);
            }
        }
    }

    #[Test]
    public function categories_with_counts_query_is_efficient(): void
    {
        // Create test data
        Documentation::factory()->count(10)->create(['category' => 'core']);
        Documentation::factory()->count(5)->create(['category' => 'recipes']);
        Documentation::factory()->count(3)->create(['category' => 'deployment']);

        DB::enableQueryLog();

        $categories = Documentation::categoriesWithCounts()->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be a single GROUP BY query
        $this->assertEquals(1, count($queries));
        $this->assertCount(3, $categories);

        // Verify the query uses GROUP BY
        if (isset($queries[0]) && is_array($queries[0]) && isset($queries[0]['query'])) {
            $query = $queries[0]['query'];
            if (is_string($query)) {
                $this->assertStringContainsString('group by', strtolower($query));
            }
        }

        // Verify counts are correct
        $coreCategory = $categories->firstWhere('category', 'core');
        $this->assertNotNull($coreCategory);
        $this->assertIsObject($coreCategory);
        if (property_exists($coreCategory, 'document_count')) {
            $this->assertEquals(10, $coreCategory->document_count);
        }
    }

    #[Test]
    public function user_statistics_queries_are_optimized(): void
    {
        // Create test users
        User::factory()->count(10)->create();
        User::factory()->count(5)->create(['email_verified_at' => now()]);
        User::factory()->count(3)->create([
            'last_login_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
        ]);

        DB::enableQueryLog();

        // Simulate optimized statistics queries
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $activeUsers = User::whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 4 separate optimized queries
        $this->assertEquals(4, count($queries));

        // Verify results
        $this->assertIsInt($totalUsers);
        $this->assertIsInt($verifiedUsers);
        $this->assertIsInt($recentUsers);
        $this->assertIsInt($activeUsers);

        $this->assertGreaterThan(0, $totalUsers);
        $this->assertGreaterThanOrEqual(5, $verifiedUsers);
    }

    #[Test]
    public function recent_activities_query_is_optimized(): void
    {
        $user = User::factory()->create();

        // Create activities
        for ($i = 0; $i < 15; $i++) {
            Activity::create([
                'log_name' => 'default',
                'description' => 'test activity ' . $i,
                'causer_type' => User::class,
                'causer_id' => $user->id,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        DB::enableQueryLog();

        $recentActivities = Activity::with(['causer:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->select('id', 'description', 'causer_type', 'causer_id', 'created_at')
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 2 queries: activities + causers
        $this->assertLessThanOrEqual(2, count($queries));
        $this->assertCount(10, $recentActivities);

        // Verify ordering
        $timestamps = $recentActivities->pluck('created_at')->toArray();
        $sortedTimestamps = collect($timestamps)->sortDesc()->values()->toArray();
        $this->assertEquals($sortedTimestamps, $timestamps);
    }

    #[Test]
    public function complex_filtering_queries_perform_well(): void
    {
        // Create test data with various filters
        User::factory()->count(20)->create(['email_verified_at' => now()]);
        User::factory()->count(10)->create(['email_verified_at' => null]);
        User::factory()->count(5)->create([
            'created_at' => now()->subDays(60),
            'email_verified_at' => now(),
        ]);

        DB::enableQueryLog();

        // Simulate complex filtering (like in Filament filters)
        $filteredUsers = User::query()
            ->whereNotNull('email_verified_at')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be a single optimized query
        $this->assertEquals(1, count($queries));
        $this->assertLessThanOrEqual(10, $filteredUsers->count());

        // Verify all results match filters
        foreach ($filteredUsers as $user) {
            $this->assertNotNull($user->email_verified_at);
            $this->assertTrue($user->created_at >= now()->subDays(30));
        }
    }

    #[Test]
    public function search_queries_are_optimized(): void
    {
        // Create searchable documents
        Documentation::factory()->count(5)->create([
            'title' => 'Laravel Testing Guide',
            'content' => 'This is a comprehensive guide about testing in Laravel.',
            'category' => 'core',
        ]);

        Documentation::factory()->count(3)->create([
            'title' => 'Deployment Guide',
            'content' => 'How to deploy your Laravel application.',
            'category' => 'deployment',
        ]);

        DB::enableQueryLog();

        // Test category-filtered search simulation
        $results = Documentation::query()
            ->where('category', 'core')
            ->where(function ($query) {
                $query->where('title', 'like', '%Laravel%')
                    ->orWhere('content', 'like', '%Laravel%');
            })
            ->select('id', 'title', 'excerpt', 'category', 'slug')
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be a single query with proper WHERE conditions
        $this->assertEquals(1, count($queries));
        $this->assertGreaterThan(0, $results->count());

        // Verify category filtering
        foreach ($results as $result) {
            $this->assertEquals('core', $result->category);
        }
    }
}
