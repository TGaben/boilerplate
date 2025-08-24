<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API általános rate limiting - 60 request/min per user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip(),
            )->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Túl sok kérés. Próbálja újra később.',
                    'error' => 'rate_limit_exceeded',
                    'retry_after' => $headers['Retry-After'] ?? null,
                ], 429, $headers);
            });
        });

        // Dokumentáció keresés rate limiting - 30 request/min per IP
        RateLimiter::for('docs-search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Túl sok keresési kérés. Várjon egy percet a folytatás előtt.',
                        'error' => 'search_rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ], 429, $headers);
                });
        });

        // Auth rate limiting - bejelentkezés védelme
        RateLimiter::for('auth', function (Request $request) {
            $email = $request->input('email');
            $emailString = is_string($email) ? strtolower($email) : 'unknown';
            $key = $request->ip() . '|' . $emailString;

            return [
                // 5 próbálkozás percenként email alapján
                Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                    $retryAfter = $headers['Retry-After'] ?? '60';
                    $retryAfterString = is_string($retryAfter) || is_numeric($retryAfter) ? (string) $retryAfter : '60';

                    return response()->json([
                        'success' => false,
                        'message' => 'Túl sok bejelentkezési kísérlet. Próbálja újra ' . $retryAfterString . ' másodperc múlva.',
                        'error' => 'auth_rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ], 429, $headers);
                }),

                // IP alapú korlátozás - 10 próbálkozás percenként
                Limit::perMinute(10)->by($request->ip())->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'IP címéről túl sok bejelentkezési kísérlet történt.',
                        'error' => 'ip_rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ], 429, $headers);
                }),
            ];
        });

        // Regisztráció rate limiting - spam védelem
        RateLimiter::for('registration', function (Request $request) {
            return [
                // IP alapján 3 regisztráció óránként
                Limit::perHour(3)->by($request->ip())->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Túl sok regisztráció történt erről az IP címről. Próbálja újra 1 óra múlva.',
                        'error' => 'registration_rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ], 429, $headers);
                }),

                // Globális korlát - 100 regisztráció óránként az egész alkalmazáshoz
                Limit::perHour(100)->by('global-registration')->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A rendszer jelenleg túlterhelt. Próbálja újra később.',
                        'error' => 'global_registration_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ], 429, $headers);
                }),
            ];
        });

        // Admin műveletekhez stricter rate limiting
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(120)->by(
                $request->user()?->id ?: $request->ip(),
            )->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin műveletek rate limit túllépve.',
                    'error' => 'admin_rate_limit_exceeded',
                    'retry_after' => $headers['Retry-After'] ?? null,
                ], 429, $headers);
            });
        });

        // Jelszó reset rate limiting
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Túl sok jelszó visszaállítási kérés. Próbálja újra később.',
                        'error' => 'password_reset_rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ], 429, $headers);
                });
        });
    }
}
