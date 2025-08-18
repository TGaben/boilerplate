<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceComprehensiveTest extends TestCase
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

    public function test_admin_can_access_user_resource_index(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/users');

        $response->assertOk();
    }

    public function test_regular_user_cannot_access_user_resource(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_user_resource(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_create_user_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/users/create');

        $response->assertOk();
    }

    public function test_admin_can_access_edit_user_page(): void
    {
        $this->actingAs($this->adminUser);

        /** @var User $testUser */
        $testUser = User::factory()->create();

        $response = $this->get("/admin/users/{$testUser->id}/edit");

        $response->assertOk();
    }

    public function test_user_resource_policy_works(): void
    {
        $this->actingAs($this->adminUser);
        $this->assertTrue(UserResource::canViewAny());
        $this->assertTrue(UserResource::canCreate());

        $this->actingAs($this->regularUser);
        $this->assertFalse(UserResource::canViewAny());
        $this->assertFalse(UserResource::canCreate());
    }

    public function test_user_can_be_assigned_roles(): void
    {
        $this->actingAs($this->adminUser);

        /** @var User $testUser */
        $testUser = User::factory()->create();
        $testUser->assignRole($this->adminRole);

        $this->assertTrue($testUser->hasRole('admin'));
    }

    public function test_user_role_permissions_work(): void
    {
        $this->actingAs($this->adminUser);

        $this->assertTrue($this->adminUser->hasRole('admin'));
        $this->assertTrue($this->adminUser->can('view_any_user'));

        $this->assertTrue($this->regularUser->hasRole('user'));
        $this->assertFalse($this->regularUser->can('view_any_user'));
    }

    public function test_admin_panel_access_control(): void
    {
        $adminPanel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertTrue($this->adminUser->canAccessPanel($adminPanel));
        $this->assertFalse($this->regularUser->canAccessPanel($adminPanel));
    }

    public function test_user_factory_works(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_model_has_required_traits(): void
    {
        $user = new User();

        // Test that traits are properly included
        $this->assertTrue(in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($user)));
        $this->assertTrue(in_array('Spatie\Activitylog\Traits\LogsActivity', class_uses_recursive($user)));
    }

    public function test_user_activity_logging_configuration(): void
    {
        $user = new User();
        $options = $user->getActivitylogOptions();

        $this->assertInstanceOf(\Spatie\Activitylog\LogOptions::class, $options);
    }

    public function test_user_password_is_hashed(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'plaintext-password',
        ]);

        $this->assertNotEquals('plaintext-password', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('plaintext-password', $user->password));
    }

    public function test_user_roles_relationship(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $this->assertTrue($user->roles->contains($this->adminRole));
    }

    public function test_multiple_users_can_have_same_role(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        /** @var User $user2 */
        $user2 = User::factory()->create();

        $user1->assignRole($this->adminRole);
        $user2->assignRole($this->adminRole);

        $this->assertTrue($user1->hasRole('admin'));
        $this->assertTrue($user2->hasRole('admin'));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole([$this->adminRole, $this->userRole]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_removing_user_role_works(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        $this->assertTrue($user->hasRole('admin'));

        $user->removeRole($this->adminRole);

        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_user_permissions_through_roles(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($this->adminRole);

        // Admin role should have all permissions
        $this->assertTrue($user->hasPermissionTo('view_any_user'));
        $this->assertTrue($user->hasPermissionTo('create_user'));
        $this->assertTrue($user->hasPermissionTo('update_user'));
        $this->assertTrue($user->hasPermissionTo('delete_user'));
    }

    public function test_user_resource_uses_correct_model(): void
    {
        $this->assertEquals(User::class, UserResource::getModel());
    }

    public function test_user_timestamps_exist(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }
}
