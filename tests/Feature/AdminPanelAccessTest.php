<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the roles and permissions seeder
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_user_without_permission_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // user role has no permissions

        $response = $this->actingAs($user)->get('/admin');

        // Should be forbidden (403) due to missing 'access admin panel' permission
        $response->assertStatus(403);
    }

    public function test_user_with_admin_role_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin'); // admin role has 'access admin panel' permission

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard'); // Dashboard (default or translated)
    }

    public function test_admin_user_from_seeder_can_access_admin_panel(): void
    {
        // Get the admin user created by the seeder
        $adminUser = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($adminUser);
        $this->assertTrue($adminUser->hasRole('admin'));
        $this->assertTrue($adminUser->can('access admin panel'));

        $response = $this->actingAs($adminUser)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_user_with_direct_permission_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('access admin panel'); // Direct permission, no role

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_user_without_any_permissions_gets_forbidden(): void
    {
        $user = User::factory()->create();
        // No roles, no permissions

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_panel_login_page_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in'); // Default English login text
    }
}
