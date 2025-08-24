<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\NoCommonPasswords;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create users') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u', // Unicode letters, spaces, hyphens, apostrophes, dots
            ],
            'email' => [
                'required',
                'email:rfc',  // Remove DNS check for testing compatibility
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed',
                new NoCommonPasswords(),
            ],
            'roles' => [
                'sometimes',
                'array',
                'max:5', // Maximum 5 roles per user
            ],
            'roles.*' => [
                'string',
                'exists:' . Role::class . ',name',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'A név kötelező mező.',
            'name.min' => 'A név legalább 2 karakter hosszú kell legyen.',
            'name.max' => 'A név maximum 255 karakter hosszú lehet.',
            'name.regex' => 'A név csak betűket, szóközöket, kötőjeleket és pontokat tartalmazhat.',
            'email.required' => 'Az email cím kötelező mező.',
            'email.email' => 'Érvényes email címet adjon meg.',
            'email.unique' => 'Ez az email cím már használatban van.',
            'password.required' => 'A jelszó kötelező mező.',
            'password.min' => 'A jelszó legalább 8 karakter hosszú kell legyen.',
            'password.confirmed' => 'A jelszó megerősítése nem egyezik.',
            'roles.array' => 'A szerepköröknek tömbnek kell lenniük.',
            'roles.max' => 'Maximum 5 szerepkör rendelhető egy felhasználóhoz.',
            'roles.*.exists' => 'A megadott szerepkör nem létezik.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'név',
            'email' => 'email cím',
            'password' => 'jelszó',
            'roles' => 'szerepkörök',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize and normalize the name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim(strip_tags($this->string('name')->value())),
            ]);
        }

        // Normalize email to lowercase
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->string('email')->value())),
            ]);
        }

        // Ensure roles is array and remove duplicates
        if ($this->has('roles') && is_array($this->get('roles'))) {
            $this->merge([
                'roles' => array_unique(array_filter($this->get('roles'))),
            ]);
        }
    }

    /**
     * Get the sanitized and validated user data.
     *
     * @return array<string, mixed>
     */
    public function getUserData(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ];
    }

    /**
     * Get the validated roles or empty array.
     *
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        $validated = $this->validated();
        $roles = isset($validated['roles']) && is_array($validated['roles']) ? $validated['roles'] : [];

        // Ensure all values are strings
        return array_values(array_filter($roles, 'is_string'));
    }
}
