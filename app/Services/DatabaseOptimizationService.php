<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Documentation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

class DatabaseOptimizationService
{
    /**
     * Get optimized documentation categories with caching.
     *
     * @return array<string, array<string, mixed>>
     */
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getCategoriesWithCounts(): array
    {
        /** @var array<string, array<string, mixed>> */
        return Cache::remember('docs.categories.optimized', 3600, function (): array {
            /** @var array<string, array<string, mixed>> $result */
            $result = Documentation::categoriesWithCounts()
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
                            'documents' => $this->getRecentDocumentsByCategory($category, 3),
                        ],
                    ];
                })->toArray();

            return $result;
        });
    }

    /**
     * Get recent documents by category with optimized query.
     *
     * @return Collection<int, Documentation>
     */
    /**
     * @return Collection<int, Documentation>
     */
    public function getRecentDocumentsByCategory(string $category, int $limit = 5): Collection
    {
        /** @var Collection<int, Documentation> */
        return Cache::remember("docs.recent.{$category}.{$limit}", 1800, function () use ($category, $limit): Collection {
            /** @var Collection<int, Documentation> $result */
            $result = Documentation::byCategory($category)
                ->recent($limit)
                ->select('id', 'title', 'slug', 'excerpt', 'category', 'updated_at')
                ->get();

            return $result;
        });
    }

    /**
     * Bulk update documentation search indexes.
     */
    /**
     * @param array<int> $documentIds
     */
    public function bulkUpdateSearchIndexes(array $documentIds): void
    {
        // Batch process in chunks to avoid memory issues
        collect($documentIds)
            ->chunk(100)
            ->each(function ($chunk) {
                Documentation::whereIn('id', $chunk->toArray())
                    ->get()
                    ->each(function (Documentation $doc) {
                        if ($doc->shouldBeSearchable()) {
                            $doc->searchable();
                        }
                    });
            });
    }

    /**
     * Get user statistics with optimized queries.
     *
     * @return array<string, int>
     */
    /**
     * @return array<string, int>
     */
    public function getUserStatistics(): array
    {
        /** @var array<string, int> */
        return Cache::remember('admin.user_statistics', 600, function (): array {
            /** @var array<string, int> $stats */
            $stats = [
                'total_users' => User::count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
                'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
                'active_users' => User::whereNotNull('last_login_at')
                    ->where('last_login_at', '>=', now()->subDays(30))
                    ->count(),
            ];

            return $stats;
        });
    }

    /**
     * Get activity log statistics with optimized queries.
     *
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getActivityStatistics(): array
    {
        /** @var array<string, mixed> */
        return Cache::remember('admin.activity_statistics', 600, function (): array {
            $recentActivities = Activity::with(['causer:id,name'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->select('id', 'description', 'causer_type', 'causer_id', 'created_at')
                ->get();

            /** @var array<string, mixed> $stats */
            $stats = [
                'total_activities' => Activity::count(),
                'recent_activities' => $recentActivities,
                'activities_today' => Activity::whereDate('created_at', today())->count(),
                'activities_this_week' => Activity::where('created_at', '>=', now()->subWeek())->count(),
            ];

            return $stats;
        });
    }

    /**
     * Clear optimization caches.
     */
    public function clearOptimizationCaches(): void
    {
        $cacheKeys = [
            'docs.categories.optimized',
            'admin.user_statistics',
            'admin.activity_statistics',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear pattern-based caches
        $this->clearPatternCache('docs.recent.*');
    }

    /**
     * Get database query performance insights.
     *
     * @return array<string, mixed>
     */
    public function getQueryPerformanceInsights(): array
    {
        return [
            'slow_queries_enabled' => (bool) DB::connection()->getConfig('slow_query_log'),
            'query_cache_enabled' => (bool) DB::connection()->getConfig('query_cache_type'),
            'table_statistics' => $this->getTableStatistics(),
            'index_usage' => $this->getIndexUsageStatistics(),
        ];
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
     * Clear caches matching a pattern.
     */
    private function clearPatternCache(string $pattern): void
    {
        // This is a simplified implementation
        // In production, you might want to use Redis SCAN or similar
        $store = Cache::getStore();
        if (method_exists($store, 'flush')) {
            // For development/testing - full flush
            // In production, implement pattern-based clearing
        }
    }

    /**
     * Get table statistics for performance monitoring.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getTableStatistics(): array
    {
        $tables = ['users', 'documentations', 'activity_log', 'roles', 'permissions'];
        $statistics = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $statistics[$table] = [
                    'row_count' => DB::table($table)->count(),
                    'avg_row_length' => null, // Would require specific DB queries
                    'data_length' => null,    // Would require specific DB queries
                ];
            }
        }

        return $statistics;
    }

    /**
     * Get index usage statistics.
     *
     * @return array<string, mixed>
     */
    private function getIndexUsageStatistics(): array
    {
        return [
            'indexes_checked' => true,
            'missing_indexes' => $this->suggestMissingIndexes(),
            'unused_indexes' => [], // Would require query log analysis
        ];
    }

    /**
     * Suggest missing indexes based on common query patterns.
     *
     * @return array<string>
     */
    private function suggestMissingIndexes(): array
    {
        $suggestions = [];

        // Check if our performance indexes exist
        if (!$this->indexExists('users', 'users_email_verified_at_index')) {
            $suggestions[] = 'users.email_verified_at - for verified user queries';
        }

        if (!$this->indexExists('users', 'users_last_login_at_index')) {
            $suggestions[] = 'users.last_login_at - for active user queries';
        }

        return $suggestions;
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            foreach ($indexes as $index) {
                if (is_object($index) && property_exists($index, 'Key_name')) {
                    /** @var object{Key_name: string} $index */
                    if ($index->Key_name === $indexName) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
