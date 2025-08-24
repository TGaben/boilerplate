<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\DocsSearchRequest;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions for testing
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'create roles']);

        // Create a test role
        Role::create(['name' => 'test-role']);
    }

    #[Test]
    public function docs_search_request_validates_correctly(): void
    {
        // Valid data
        $validData = [
            'q' => 'test query',
            'category' => 'core',
            'limit' => 10,
        ];

        $request = new DocsSearchRequest();
        $validator = Validator::make($validData, $request->rules());

        $this->assertFalse($validator->fails());

        // Invalid data - empty query
        $invalidData = [
            'q' => '',
            'category' => 'invalid-category',
            'limit' => 100,
        ];

        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('q', $validator->errors()->toArray());
        $this->assertArrayHasKey('category', $validator->errors()->toArray());
        $this->assertArrayHasKey('limit', $validator->errors()->toArray());
    }

    #[Test]
    public function docs_search_request_sanitizes_input(): void
    {
        // Test with safe query that should pass validation
        $response = $this->getJson('/docs/search?q=' . urlencode('safe test query') . '&limit=15');

        // Should work with safe content
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('query', $data);
        $this->assertEquals('safe test query', $data['query'] ?? '');

        // Test that dangerous characters are rejected by validation
        $response = $this->getJson('/docs/search?q=' . urlencode('<script>alert("xss")</script>'));
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['q']);
    }

    #[Test]
    public function user_store_request_validates_correctly(): void
    {
        // Test with valid strong password that passes all rules including NoCommonPasswords
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'MyVerySecureP@ssw0rd2024!', // Strong, unique password
            'password_confirmation' => 'MyVerySecureP@ssw0rd2024!',
            'roles' => ['test-role'],
        ];

        $request = new UserStoreRequest();
        $validator = Validator::make($validData, $request->rules());

        // Manually check if validation would fail and why
        if ($validator->fails()) {
            // Check if it's due to email:rfc,dns rule which requires DNS resolution in tests
            $errors = $validator->errors()->toArray();
            $emailErrorsJson = json_encode($errors['email'] ?? []);
            $this->assertTrue(isset($errors['email']) && is_string($emailErrorsJson) && str_contains($emailErrorsJson, 'DNS'), 'Validation failed for unexpected reason: ' . json_encode($errors));
        } else {
            $this->assertFalse($validator->fails());
        }

        // Invalid data - common password and other validation errors
        $invalidData = [
            'name' => 'J',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'roles' => ['non-existent-role'],
        ];

        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertArrayHasKey('roles.0', $validator->errors()->toArray());
    }

    #[Test]
    public function user_store_request_rejects_common_passwords(): void
    {
        $commonPasswords = [
            'password',
            '123456',
            'qwerty',
            'admin',
        ];

        $request = new UserStoreRequest();

        foreach ($commonPasswords as $password) {
            $data = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ];

            $validator = Validator::make($data, $request->rules());
            $this->assertTrue($validator->fails(), "Password '{$password}' should be rejected");
            $this->assertArrayHasKey('password', $validator->errors()->toArray());
        }
    }

    #[Test]
    public function user_update_request_validates_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        // Valid partial update
        $validData = [
            'name' => 'Updated Name',
            'password' => 'MyVerySecureP@ssw0rd2024!',
            'password_confirmation' => 'MyVerySecureP@ssw0rd2024!',
        ];

        // Create a proper mock route with bound parameters
        $route = new \Illuminate\Routing\Route(['PATCH'], '', []);
        $route->bind(new \Illuminate\Http\Request());
        $route->setParameter('user', (string) $user->id);

        $request = new UserUpdateRequest();
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $validator = Validator::make($validData, $request->rules());

        // Check validation result (might fail due to DNS email check)
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            // Expect either no errors or only DNS-related email validation errors
            $emailErrorsJson = json_encode($errors['email'] ?? []);
            $this->assertTrue(
                empty($errors) || (isset($errors['email']) && is_string($emailErrorsJson) && str_contains($emailErrorsJson, 'DNS')),
                'Unexpected validation errors: ' . json_encode($errors),
            );
        } else {
            $this->assertFalse($validator->fails());
        }

        // Test email uniqueness ignoring current user
        $emailData = [
            'email' => 'existing@example.com', // Same as current user's email
        ];

        $validator = Validator::make($emailData, $request->rules());
        // This should not fail for uniqueness, but might fail for DNS
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $emailErrorsJson = json_encode($errors['email'] ?? []);
            $this->assertFalse(isset($errors['email']) && is_string($emailErrorsJson) && str_contains($emailErrorsJson, 'unique'), 'Email uniqueness should ignore current user');
        }
    }

    #[Test]
    public function role_store_request_validates_correctly(): void
    {
        Permission::create(['name' => 'test permission']);

        // Valid data
        $validData = [
            'name' => 'new-role',
            'permissions' => ['test permission'],
        ];

        $request = new RoleStoreRequest();
        $validator = Validator::make($validData, $request->rules());

        $this->assertFalse($validator->fails());

        // Invalid data - duplicate role name
        Role::create(['name' => 'existing-role']);

        $invalidData = [
            'name' => 'existing-role',
            'permissions' => ['non-existent-permission'],
        ];

        $validator = Validator::make($invalidData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('permissions.0', $validator->errors()->toArray());
    }

    #[Test]
    public function role_store_request_sanitizes_input(): void
    {
        // Create the permission for this specific test
        Permission::create(['name' => 'test sanitize permission']);

        // Test that role name validation works correctly
        $uniqueRoleName = 'unique-test-role-' . uniqid(); // Ensure unique name
        $validData = [
            'name' => $uniqueRoleName,
            'permissions' => ['test sanitize permission'],
        ];

        $request = new RoleStoreRequest();
        $validator = Validator::make($validData, $request->rules(), $request->messages());

        // Should pass validation with unique name and valid permissions
        $this->assertFalse($validator->fails(), 'Role validation should pass with valid unique data');

        // Test regex validation rejects invalid characters
        $invalidData = [
            'name' => 'invalid@role#name',  // Contains invalid characters for the regex
            'permissions' => ['test sanitize permission'],
        ];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertTrue($validator->fails(), 'Role validation should fail with invalid characters');
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function user_store_request_helper_methods_work(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'test@localhost',  // Use localhost to avoid DNS issues
            'password' => 'MyVerySecureP@ssw0rd2024!',
            'password_confirmation' => 'MyVerySecureP@ssw0rd2024!',
            'roles' => ['test-role'],
        ];

        // Create a simple validator without DNS checking for testing
        $rules = [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email',  // Remove DNS check for test
            'password' => 'required|string|confirmed',
            'roles' => 'sometimes|array',
        ];

        $request = new UserStoreRequest($data);
        $request->setValidator(Validator::make($data, $rules));

        $userData = $request->getUserData();
        $this->assertArrayHasKey('name', $userData);
        $this->assertArrayHasKey('email', $userData);
        $this->assertArrayHasKey('password', $userData);
        $this->assertArrayHasKey('email_verified_at', $userData);

        $roles = $request->getRoles();
        $this->assertEquals(['test-role'], $roles);
    }

    #[Test]
    public function user_update_request_helper_methods_work(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
        ];

        // Create a proper mock route with bound parameters
        $route = new \Illuminate\Routing\Route(['PATCH'], '', []);
        $route->bind(new \Illuminate\Http\Request());
        $route->setParameter('user', (string) $user->id);

        $rules = [
            'name' => 'sometimes|required|string|min:2|max:255',
        ];

        $request = new UserUpdateRequest($data);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
        $request->setValidator(Validator::make($data, $rules));

        $userData = $request->getUserData();
        $this->assertArrayHasKey('name', $userData);
        $this->assertArrayNotHasKey('email', $userData);
        $this->assertArrayNotHasKey('password', $userData);

        $this->assertFalse($request->shouldUpdatePassword());

        // With password
        $dataWithPassword = [
            'name' => 'Updated Name',
            'password' => 'MyVerySecureP@ssw0rd2024!',
            'password_confirmation' => 'MyVerySecureP@ssw0rd2024!',
        ];

        $rulesWithPassword = [
            'name' => 'sometimes|required|string|min:2|max:255',
            'password' => 'sometimes|nullable|string|confirmed',
        ];

        $request2 = new UserUpdateRequest($dataWithPassword);
        $request2->setRouteResolver(function () use ($route) {
            return $route;
        });
        $request2->setValidator(Validator::make($dataWithPassword, $rulesWithPassword));

        $this->assertTrue($request2->shouldUpdatePassword());
    }

    #[Test]
    public function role_store_request_helper_methods_work(): void
    {
        Permission::create(['name' => 'test permission']);

        $data = [
            'name' => 'unique-test-role-' . uniqid(),  // Ensure uniqueness
            'permissions' => ['test permission'],
        ];

        $rules = [
            'name' => 'required|string|min:2|max:255',
            'permissions' => 'sometimes|array',
        ];

        $request = new RoleStoreRequest($data);
        $request->setValidator(Validator::make($data, $rules));

        $roleData = $request->getRoleData();
        $this->assertArrayHasKey('name', $roleData);
        $this->assertArrayHasKey('guard_name', $roleData);
        $this->assertEquals('web', $roleData['guard_name']);

        $permissions = $request->getPermissions();
        $this->assertEquals(['test permission'], $permissions);
    }
}
