<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class EnvironmentValidator
{
    /** @var array<string, mixed> */
    protected array $config;

    /** @var array<string, string> */
    protected array $environment;

    /** @var array<string, list<string>> */
    protected array $errors = [];

    /** @var array<string, list<string>> */
    protected array $warnings = [];

    public function __construct()
    {
        $config = config('environment.validation');
        $this->config = is_array($config) ? $config : [];

        // Cast $_ENV to proper type
        $this->environment = array_map(fn ($value) => (string) $value, $_ENV);
    }

    /**
     * Validate the current environment configuration
     *
     * @return array{valid: bool, errors: array<string, list<string>>, warnings: array<string, list<string>>, environment: string}
     */
    public function validate(?string $targetEnv = null): array
    {
        $this->errors = [];
        $this->warnings = [];

        $currentEnv = $targetEnv ?? $this->environment['APP_ENV'] ?? 'local';

        $this->validateRequired();
        $this->validateEnvironmentSpecific($currentEnv);
        $this->validateSecurity($currentEnv);
        $this->validateCustomization();

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'environment' => $currentEnv,
        ];
    }

    /**
     * Validate required environment variables
     */
    protected function validateRequired(): void
    {
        $required = $this->config['required'] ?? [];

        if (!is_array($required)) {
            return;
        }

        foreach ($required as $key => $rules) {
            if (!is_string($key) || !is_string($rules)) {
                continue;
            }
            $value = $this->getEnvValue($key);

            if ($value === null) {
                $this->addError($key, "Required environment variable '{$key}' is missing");
                continue;
            }

            $this->validateValue($key, $value, $rules);
        }
    }

    /**
     * Validate environment-specific required variables
     */
    protected function validateEnvironmentSpecific(string $env): void
    {
        $requiredByEnv = $this->config['required_by_env'][$env] ?? [];

        if (!is_array($requiredByEnv)) {
            return;
        }

        foreach ($requiredByEnv as $key => $rules) {
            if (!is_string($key) || !is_string($rules)) {
                continue;
            }
            $value = $this->getEnvValue($key);

            if ($value === null) {
                $this->addError($key, "Required environment variable '{$key}' is missing for {$env} environment");
                continue;
            }

            $this->validateValue($key, $value, $rules);
        }
    }

    /**
     * Validate security-sensitive variables
     */
    protected function validateSecurity(string $env): void
    {
        $securitySensitive = $this->config['security_sensitive'] ?? [];

        foreach ($securitySensitive as $key => $config) {
            $value = $this->getEnvValue($key);

            if ($value === null) {
                continue; // Will be caught by required validation
            }

            // Check if this validation only applies to production
            if ($config['production_only'] && $env !== 'production') {
                continue;
            }

            // Check forbidden values
            $forbiddenValues = $config['forbidden_values'] ?? [];
            if (in_array($value, $forbiddenValues, true)) {
                $this->addError($key, "Environment variable '{$key}' contains a forbidden/default value");
            }

            // Additional security checks
            if (is_string($value)) {
                $this->validateSecurityStrength($key, $value, $env);
            }
        }
    }

    /**
     * Validate customization recommendations
     */
    protected function validateCustomization(): void
    {
        $shouldCustomize = $this->config['should_customize'] ?? [];

        foreach ($shouldCustomize as $key => $config) {
            $value = $this->getEnvValue($key);

            if ($value === null) {
                continue;
            }

            $defaultValues = $config['default_values'] ?? [];
            if (in_array($value, $defaultValues, true)) {
                $warning = $config['warning'] ?? "Consider customizing '{$key}' for your project";
                $this->addWarning($key, $warning);
            }
        }
    }

    /**
     * Validate a specific value against rules
     */
    protected function validateValue(string $key, mixed $value, string $rules): void
    {
        $validator = Validator::make(
            [$key => $value],
            [$key => $rules],
            [],
            [$key => $key],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->get($key) as $error) {
                $errorMessage = is_string($error) ? $error : 'Validation error';
                $this->addError($key, $errorMessage);
            }
        }
    }

    /**
     * Additional security strength validation
     */
    protected function validateSecurityStrength(string $key, string $value, string $env): void
    {
        if ($env !== 'production') {
            return;
        }

        switch ($key) {
            case 'APP_KEY':
                if (strlen($value) < 32) {
                    $this->addError($key, 'APP_KEY must be at least 32 characters long');
                }
                break;

            case 'DB_PASSWORD':
                if (strlen($value) < 8) {
                    $this->addError($key, 'Database password should be at least 8 characters long in production');
                }
                break;

            case 'REDIS_PASSWORD':
                if (strlen($value) < 8) {
                    $this->addError($key, 'Redis password should be at least 8 characters long in production');
                }
                break;
        }
    }

    /**
     * Get environment value with type conversion
     */
    protected function getEnvValue(string $key): mixed
    {
        $value = $this->environment[$key] ?? null;

        if ($value === null) {
            return null;
        }

        // Convert string representations to proper types
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }

    /**
     * Add validation error
     */
    protected function addError(string $key, string $message): void
    {
        if (!isset($this->errors[$key])) {
            $this->errors[$key] = [];
        }
        $this->errors[$key][] = $message;
    }

    /**
     * Add validation warning
     */
    protected function addWarning(string $key, string $message): void
    {
        if (!isset($this->warnings[$key])) {
            $this->warnings[$key] = [];
        }
        $this->warnings[$key][] = $message;
    }

    /**
     * Get validation summary
     *
     * @return array{status: string, environment: string, error_count: int, warning_count: int, total_checked: int}
     */
    public function getSummary(): array
    {
        $validation = $this->validate();

        return [
            'status' => $validation['valid'] ? 'valid' : 'invalid',
            'environment' => $validation['environment'],
            'error_count' => count($validation['errors']),
            'warning_count' => count($validation['warnings']),
            'total_checked' => count($this->config['required'] ?? []) +
                              count($this->config['required_by_env'][$validation['environment']] ?? []),
        ];
    }

    /**
     * Check if a specific template file exists
     */
    public function templateExists(string $template): bool
    {
        $templatesConfig = config('environment.templates');
        if (!is_array($templatesConfig)) {
            return false;
        }

        $available = $templatesConfig['available'] ?? [];
        if (!is_array($available) || !isset($available[$template])) {
            return false;
        }

        $templateInfo = $available[$template];
        if (!is_array($templateInfo) || !isset($templateInfo['file'])) {
            return false;
        }

        $templatePath = ($templatesConfig['path'] ?? '') . '/' . $templateInfo['file'];

        return file_exists($templatePath);
    }

    /**
     * Get available templates
     *
     * @return array<string, mixed>
     */
    public function getAvailableTemplates(): array
    {
        $available = config('environment.templates.available');

        return is_array($available) ? $available : [];
    }

    /**
     * Load template content
     */
    public function getTemplateContent(string $template): ?string
    {
        if (!$this->templateExists($template)) {
            return null;
        }

        $templatesConfig = config('environment.templates');
        if (!is_array($templatesConfig)) {
            return null;
        }

        $available = $templatesConfig['available'] ?? [];
        if (!is_array($available) || !isset($available[$template])) {
            return null;
        }

        $templateInfo = $available[$template];
        if (!is_array($templateInfo) || !isset($templateInfo['file'])) {
            return null;
        }

        $templatePath = ($templatesConfig['path'] ?? '') . '/' . $templateInfo['file'];

        $content = file_get_contents($templatePath);

        return $content !== false ? $content : null;
    }
}
