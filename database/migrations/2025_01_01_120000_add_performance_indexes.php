<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add performance indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email_verified_at']);
            $table->index(['last_login_at']);
            $table->index(['created_at']);
            $table->index(['updated_at']);
        });

        // Add performance indexes to activity_log table (if not already present)
        $activityTable = config('activitylog.table_name', 'activity_log');
        if (is_string($activityTable) && Schema::hasTable($activityTable)) {
            Schema::table($activityTable, function (Blueprint $table) {
                $table->index(['created_at']);
                $table->index(['causer_type', 'causer_id']);
                $table->index(['subject_type', 'subject_id']);
                $table->index(['description']);
            });
        }

        // Add performance indexes to role and permission tables
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->index(['guard_name']);
                $table->index(['created_at']);
            });
        }

        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->index(['guard_name']);
                $table->index(['created_at']);
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->index(['model_type', 'model_id']);
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->index(['model_type', 'model_id']);
            });
        }

        // Add compound indexes for better query performance
        if (Schema::hasTable('documentations')) {
            Schema::table('documentations', function (Blueprint $table) {
                $table->index(['created_at']);
                $table->index(['updated_at']);
                $table->index(['category', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop performance indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email_verified_at']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });

        // Drop performance indexes from activity_log table
        $activityTable = config('activitylog.table_name', 'activity_log');
        if (is_string($activityTable) && Schema::hasTable($activityTable)) {
            Schema::table($activityTable, function (Blueprint $table) {
                $table->dropIndex(['created_at']);
                $table->dropIndex(['causer_type', 'causer_id']);
                $table->dropIndex(['subject_type', 'subject_id']);
                $table->dropIndex(['description']);
            });
        }

        // Drop performance indexes from role and permission tables
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex(['guard_name']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('permissions')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropIndex(['guard_name']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropIndex(['model_type', 'model_id']);
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropIndex(['model_type', 'model_id']);
            });
        }

        // Drop compound indexes
        if (Schema::hasTable('documentations')) {
            Schema::table('documentations', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
                $table->dropIndex(['updated_at']);
                $table->dropIndex(['category', 'created_at']);
            });
        }
    }
};
