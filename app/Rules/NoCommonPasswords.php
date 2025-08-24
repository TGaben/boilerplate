<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoCommonPasswords implements ValidationRule
{
    /**
     * Most common passwords that should be rejected.
     *
     * @var array<int, string>
     */
    private const COMMON_PASSWORDS = [
        'password',
        '123456',
        '123456789',
        '12345678',
        '12345',
        'qwerty',
        'abc123',
        'password123',
        'admin',
        'letmein',
        'welcome',
        'monkey',
        '1234567890',
        'iloveyou',
        'princess',
        'rockyou',
        'dragon',
        'sunshine',
        'master',
        'shadow',
        'football',
        'baseball',
        'superman',
        'michael',
        'jordan23',
        'harley',
        'ranger',
        'hunter',
        'buster',
        'thomas',
        'robert',
        'joshua',
        'daniel',
        'matthew',
        'anthony',
        'william',
        'jennifer',
        'amanda',
        'melissa',
        'jessica',
        'ashley',
        'brittany',
        'mustang',
        'access',
        'shadow',
        'master',
        'killer',
        'computer',
        'trustno1',
        'freedom',
        'whatever',
        'internet',
        'service',
        'server',
        'system',
    ];

    /**
     * Common patterns that should be rejected.
     *
     * @var array<int, string>
     */
    private const COMMON_PATTERNS = [
        '/^password/i',
        '/^admin/i',
        '/^user/i',
        '/^test/i',
        '/^guest/i',
        '/^\d{4,}$/', // Only numbers
        '/^[a-z]+$/', // Only lowercase letters
        '/^[A-Z]+$/', // Only uppercase letters
        '/^(.)\\1{3,}$/', // Repeated characters (aaaa, 1111, etc.)
        '/^(123|abc|qwe)/i', // Sequential patterns
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        // Convert to lowercase for comparison
        $lowercaseValue = strtolower($value);

        // Check against common passwords list
        if (in_array($lowercaseValue, self::COMMON_PASSWORDS, true)) {
            $fail('validation.no_common_passwords')->translate([
                'attribute' => $attribute,
            ]);

            return;
        }

        // Check against common patterns
        foreach (self::COMMON_PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                $fail('validation.no_common_passwords')->translate([
                    'attribute' => $attribute,
                ]);

                return;
            }
        }

        // Check for keyboard patterns
        if ($this->isKeyboardPattern($value)) {
            $fail('validation.no_common_passwords')->translate([
                'attribute' => $attribute,
            ]);
        }
    }

    /**
     * Check if the value matches common keyboard patterns.
     */
    private function isKeyboardPattern(string $value): bool
    {
        $keyboardPatterns = [
            'qwerty',
            'asdf',
            'zxcv',
            '1234',
            'abcd',
            '!@#$',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
            '1234567890',
        ];

        $lowercaseValue = strtolower($value);

        foreach ($keyboardPatterns as $pattern) {
            // Check if password starts with or contains the pattern
            if (str_contains($lowercaseValue, $pattern)) {
                return true;
            }

            // Check reverse pattern
            if (str_contains($lowercaseValue, strrev($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'A :attribute túl gyakori vagy könnyen kitalálható. Válasszon biztonságosabb jelszót.';
    }
}
