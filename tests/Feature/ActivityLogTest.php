<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_model_changes_are_logged(): void
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        // Verify initial activity log
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'Felhasználó created',
        ]);

        // Update the user
        $user->update([
            'name' => 'Updated Name',
        ]);

        // Verify update activity log
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'Felhasználó updated',
        ]);

        // Verify the activity log contains changed attributes
        $activity = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('description', 'Felhasználó updated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertArrayHasKey('name', $activity->properties['attributes']);
        $this->assertEquals('Updated Name', $activity->properties['attributes']['name']);
        $this->assertEquals('Original Name', $activity->properties['old']['name']);
    }

    #[Test]
    public function role_model_changes_are_logged(): void
    {
        $role = Role::create([
            'name' => 'test-role',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'description' => 'Szerepkör created',
        ]);

        $role->update(['name' => 'updated-role']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'description' => 'Szerepkör updated',
        ]);
    }

    #[Test]
    public function permission_model_changes_are_logged(): void
    {
        $permission = Permission::create([
            'name' => 'test-permission',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Permission::class,
            'subject_id' => $permission->id,
            'description' => 'Jogosultság created',
        ]);

        $permission->update(['name' => 'updated-permission']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Permission::class,
            'subject_id' => $permission->id,
            'description' => 'Jogosultság updated',
        ]);
    }

    #[Test]
    public function only_dirty_attributes_are_logged(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Clear existing activities
        Activity::truncate();

        // Update with the same values (no real changes)
        $user->update([
            'name' => 'Test User', // Same value
            'email' => 'newemail@example.com', // Different value
        ]);

        // Should have one activity log for the update
        $activities = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('description', 'Felhasználó updated')
            ->get();

        $this->assertCount(1, $activities);

        $activity = $activities->first();

        // Only email should be in the logged attributes (name didn't change)
        $this->assertArrayHasKey('email', $activity->properties['attributes']);
        $this->assertArrayNotHasKey('name', $activity->properties['attributes']);
    }

    #[Test]
    public function activity_log_resource_is_read_only_for_admin(): void
    {
        // Create roles first
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $activity = Activity::first();
        if (!$activity) {
            $activity = new Activity();
        }

        $activityPolicy = new \App\Policies\ActivityPolicy();

        $this->assertTrue($activityPolicy->viewAny($admin));
        $this->assertTrue($activityPolicy->view($admin, $activity));
        $this->assertFalse($activityPolicy->create($admin));
        $this->assertFalse($activityPolicy->update($admin, $activity));
        $this->assertFalse($activityPolicy->delete($admin, $activity));
    }

    #[Test]
    public function non_admin_user_cannot_access_activity_log(): void
    {
        // Create roles first
        Role::create(['name' => 'user', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('user');

        $activity = Activity::first();
        if (!$activity) {
            $activity = new Activity();
        }

        $activityPolicy = new \App\Policies\ActivityPolicy();

        $this->assertFalse($activityPolicy->viewAny($user));
        $this->assertFalse($activityPolicy->view($user, $activity));
    }
}
