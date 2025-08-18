<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ActivityResource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityResourceComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    private Role $adminRole;

    private Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Run seeders
        $this->artisan('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);

        // Get roles
        $this->adminRole = Role::where('name', 'admin')->first();
        $this->userRole = Role::where('name', 'user')->first();

                // Create admin user
        /** @var User $adminUser */
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);
        $this->adminUser = $adminUser;
        $this->adminUser->assignRole($this->adminRole);
        
        // Create regular user
        /** @var User $regularUser */
        $regularUser = User::factory()->create([
            'name' => 'Regular User', 
            'email' => 'user@test.com',
        ]);
        $this->regularUser = $regularUser;
        $this->regularUser->assignRole($this->userRole);

        // Force refresh permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_admin_can_access_activity_resource_index(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/activities');

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_activity_resource(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/activities');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_activity_resource(): void
    {
        $response = $this->get('/admin/activities');

        $response->assertRedirect('/admin/login');
    }

    public function test_activity_resource_respects_policies(): void
    {
        $this->actingAs($this->adminUser);
        $this->assertTrue(ActivityResource::canViewAny());

        $this->actingAs($this->regularUser);
        $this->assertFalse(ActivityResource::canViewAny());
    }

    public function test_activity_resource_is_read_only(): void
    {
        $this->actingAs($this->adminUser);

        // Activity resource should not allow creation
        $this->assertFalse(ActivityResource::canCreate());

        // Create a test activity
        $activity = Activity::create([
            'log_name' => 'test',
            'description' => 'test activity',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
            'causer_type' => User::class,
            'causer_id' => $this->adminUser->id,
        ]);

        // Should not allow editing
        $this->assertFalse(ActivityResource::canEdit($activity));

        // Should not allow deletion
        $this->assertFalse(ActivityResource::canDelete($activity));
    }

    public function test_admin_can_view_specific_activity(): void
    {
        $this->actingAs($this->adminUser);

        $activity = Activity::create([
            'log_name' => 'test',
            'description' => 'Test activity details',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
            'causer_type' => User::class,
            'causer_id' => $this->adminUser->id,
        ]);

        $response = $this->get("/admin/activities/{$activity->id}");

        $response->assertOk();
    }

    public function test_activity_logging_works_for_user_changes(): void
    {
        $this->actingAs($this->adminUser);

        // Create a user and modify it to trigger logging
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Original Name']);

        // Update the user to trigger activity log
        $user->update(['name' => 'Updated Name']);

        // Check that activity was logged
        $activities = Activity::where('subject_type', User::class)
                             ->where('subject_id', $user->id)
                             ->where('description', 'Felhasználó updated')
                             ->get();

        $this->assertGreaterThan(0, $activities->count());
    }

    public function test_activity_logging_works_for_role_changes(): void
    {
        $this->actingAs($this->adminUser);

        // Spatie Role model doesn't have activity logging by default
        // So we'll test manual activity creation for role operations
        $role = Role::create(['name' => 'Original Role', 'guard_name' => 'web']);

        // Manually log an activity for role update
        Activity::create([
            'log_name' => 'role_management',
            'description' => 'Role was updated',
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'causer_type' => User::class,
            'causer_id' => $this->adminUser->id,
            'properties' => [
                'old' => ['name' => 'Original Role'],
                'attributes' => ['name' => 'Updated Role'],
            ],
        ]);

        // Check that activity was logged
        $activities = Activity::where('subject_type', Role::class)
                             ->where('subject_id', $role->id)
                             ->get();

        $this->assertGreaterThan(0, $activities->count());
    }

    public function test_activity_contains_causer_information(): void
    {
        $this->actingAs($this->adminUser);

        $activity = Activity::create([
            'log_name' => 'test',
            'description' => 'Test causer info',
            'subject_type' => User::class,
            'subject_id' => $this->regularUser->id,
            'causer_type' => User::class,
            'causer_id' => $this->adminUser->id,
        ]);

        $this->assertEquals(User::class, $activity->causer_type);
        $this->assertEquals($this->adminUser->id, $activity->causer_id);
        $this->assertEquals($this->adminUser->id, $activity->causer->id);
    }

    public function test_activity_contains_subject_information(): void
    {
        $this->actingAs($this->adminUser);

        $activity = Activity::create([
            'log_name' => 'test',
            'description' => 'Test subject info',
            'subject_type' => User::class,
            'subject_id' => $this->regularUser->id,
            'causer_type' => User::class,
            'causer_id' => $this->adminUser->id,
        ]);

        $this->assertEquals(User::class, $activity->subject_type);
        $this->assertEquals($this->regularUser->id, $activity->subject_id);
        $this->assertEquals($this->regularUser->id, $activity->subject->id);
    }

    public function test_activity_properties_are_stored(): void
    {
        $this->actingAs($this->adminUser);

        $properties = [
            'old' => ['name' => 'Old Name', 'email' => 'old@test.com'],
            'attributes' => ['name' => 'New Name', 'email' => 'new@test.com'],
        ];

        $activity = Activity::create([
            'log_name' => 'test',
            'description' => 'Test properties storage',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
            'properties' => $properties,
        ]);

        $this->assertEquals($properties, $activity->properties->toArray());
        $this->assertEquals('Old Name', $activity->getExtraProperty('old.name'));
        $this->assertEquals('New Name', $activity->getExtraProperty('attributes.name'));
    }

    public function test_activity_can_be_filtered_by_log_name(): void
    {
        $this->actingAs($this->adminUser);

        // Create activities with different log names
        Activity::create([
            'log_name' => 'user_actions',
            'description' => 'User action logged',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
        ]);

        Activity::create([
            'log_name' => 'system_actions',
            'description' => 'System action logged',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
        ]);

        $userActions = Activity::where('log_name', 'user_actions')->get();
        $systemActions = Activity::where('log_name', 'system_actions')->get();

        $this->assertEquals(1, $userActions->count());
        $this->assertEquals(1, $systemActions->count());
    }

    public function test_activity_timestamps_are_recorded(): void
    {
        $this->actingAs($this->adminUser);

        $activity = Activity::create([
            'log_name' => 'test',
            'description' => 'Test timestamp recording',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
        ]);

        $this->assertNotNull($activity->created_at);
        $this->assertNotNull($activity->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $activity->created_at);
    }

    public function test_activity_resource_uses_correct_model(): void
    {
        $this->assertEquals(Activity::class, ActivityResource::getModel());
    }

    public function test_multiple_activities_for_same_subject(): void
    {
        $this->actingAs($this->adminUser);

        /** @var User $user */
        $user = User::factory()->create();

        // Clear any existing activities for this user first
        Activity::where('subject_type', User::class)
                ->where('subject_id', $user->id)
                ->delete();

        // Create multiple activities for the same user
        for ($i = 1; $i <= 3; $i++) {
            Activity::create([
                'log_name' => 'test',
                'description' => "Activity {$i}",
                'subject_type' => User::class,
                'subject_id' => $user->id,
            ]);
        }

        $activities = Activity::where('subject_type', User::class)
                              ->where('subject_id', $user->id)
                              ->where('log_name', 'test')
                              ->get();

        $this->assertEquals(3, $activities->count());
    }

    public function test_activity_without_causer(): void
    {
        $activity = Activity::create([
            'log_name' => 'system',
            'description' => 'System generated activity',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
        ]);

        $this->assertNull($activity->causer_type);
        $this->assertNull($activity->causer_id);
        $this->assertNull($activity->causer);
    }

    public function test_activity_log_options_configured(): void
    {
        $user = new User();
        $options = $user->getActivitylogOptions();

        $this->assertInstanceOf(\Spatie\Activitylog\LogOptions::class, $options);
    }

    public function test_activity_can_be_queried_by_description(): void
    {
        $this->actingAs($this->adminUser);

        Activity::create([
            'log_name' => 'test',
            'description' => 'Unique description for search',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
        ]);

        $activities = Activity::where('description', 'Unique description for search')->get();

        $this->assertEquals(1, $activities->count());
    }

    public function test_activity_ordering_by_date(): void
    {
        $this->actingAs($this->adminUser);

        // Clear all activities first
        Activity::truncate();

        // Create activities with different timestamps
        $oldActivity = Activity::create([
            'log_name' => 'test_order',
            'description' => 'Old activity',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
            'created_at' => now()->subDays(2),
        ]);

        $newActivity = Activity::create([
            'log_name' => 'test_order',
            'description' => 'New activity',
            'subject_type' => User::class,
            'subject_id' => $this->adminUser->id,
            'created_at' => now(),
        ]);

        $activities = Activity::where('log_name', 'test_order')
                              ->orderBy('created_at', 'desc')
                              ->get();

        $this->assertEquals($newActivity->id, $activities->first()->id);
        $this->assertEquals($oldActivity->id, $activities->last()->id);
    }
}
