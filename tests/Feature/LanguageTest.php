<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    #[Test]
    public function user_can_switch_to_hungarian_language(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)
            ->post(route('language.switch', 'hu'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'hu');
        $this->assertEquals('hu', session('locale'));
    }

    #[Test]
    public function user_can_switch_to_english_language(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)
            ->post(route('language.switch', 'en'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $this->assertEquals('en', session('locale'));
    }

    #[Test]
    public function unsupported_language_returns_error(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)
            ->post(route('language.switch', 'invalid'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Language not supported.');
    }

    #[Test]
    public function guest_can_switch_languages(): void
    {
        $response = $this->post(route('language.switch', 'en'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
    }

    #[Test]
    public function middleware_sets_locale_from_session(): void
    {
        // Set Hungarian in session
        session(['locale' => 'hu']);

        $response = $this->get('/');

        $this->assertEquals('hu', app()->getLocale());
    }

    #[Test]
    public function middleware_falls_back_to_default_locale(): void
    {
        // Clear any session locale
        session()->forget('locale');

        $response = $this->get('/');

        $this->assertEquals('hu', app()->getLocale()); // Default should be Hungarian
    }

    #[Test]
    public function middleware_rejects_invalid_locale(): void
    {
        // Set invalid locale in session
        session(['locale' => 'invalid']);

        $response = $this->get('/');

        $this->assertEquals('hu', app()->getLocale()); // Should fallback to default
    }

    #[Test]
    public function language_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('language.switch'));
    }

    #[Test]
    public function translation_files_exist(): void
    {
        $this->assertFileExists(lang_path('hu/messages.php'));
        $this->assertFileExists(lang_path('en/messages.php'));
    }

    #[Test]
    public function hungarian_translations_work(): void
    {
        app()->setLocale('hu');

        $this->assertEquals('Felhasználók', __('messages.Users'));
        $this->assertEquals('Szerepkörök', __('messages.Roles'));
        $this->assertEquals('Jogosultságok', __('messages.Permissions'));
        $this->assertEquals('Adminisztráció', __('messages.Administration'));
    }

    #[Test]
    public function english_translations_work(): void
    {
        app()->setLocale('en');

        $this->assertEquals('Users', __('messages.Users'));
        $this->assertEquals('Roles', __('messages.Roles'));
        $this->assertEquals('Permissions', __('messages.Permissions'));
        $this->assertEquals('Administration', __('messages.Administration'));
    }

    #[Test]
    public function config_languages_file_exists(): void
    {
        $this->assertFileExists(config_path('languages.php'));

        $config = config('languages');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('available', $config);
        $this->assertArrayHasKey('hu', $config['available']);
        $this->assertArrayHasKey('en', $config['available']);
    }
}
