<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class RoleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create roles') ?? false;
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
                'regex:/^[a-z0-9\-_\s]+$/i', // Alphanumeric, hyphens, underscores, spaces
                'unique:roles,name',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api',
            ],
            'permissions' => [
                'sometimes',
                'array',
                'max:50', // Maximum 50 permissions per role
            ],
            'permissions.*' => [
                'string',
                'exists:' . Permission::class . ',name',
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
            'name.required' => 'A szerepkör neve kötelező mező.',
            'name.min' => 'A szerepkör neve legalább 2 karakter hosszú kell legyen.',
            'name.max' => 'A szerepkör neve maximum 255 karakter hosszú lehet.',
            'name.regex' => 'A szerepkör neve csak betűket, számokat, kötőjeleket, aláhúzásokat és szóközöket tartalmazhat.',
            'name.unique' => 'Ez a szerepkör név már használatban van.',
            'guard_name.in' => 'A guard típus csak web vagy api lehet.',
            'permissions.array' => 'A jogosultságoknak tömbnek kell lenniük.',
            'permissions.max' => 'Maximum 50 jogosultság rendelhető egy szerepkörhöz.',
            'permissions.*.exists' => 'A megadott jogosultság nem létezik.',
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
            'name' => 'szerepkör neve',
            'guard_name' => 'guard típus',
            'permissions' => 'jogosultságok',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize and normalize the role name
        if ($this->has('name')) {
            $this->merge([
                'name' => strtolower(trim(strip_tags($this->string('name')->value()))),
            ]);
        }

        // Set default guard_name if not provided
        if (!$this->has('guard_name')) {
            $this->merge([
                'guard_name' => 'web',
            ]);
        }

        // Ensure permissions is array and remove duplicates
        if ($this->has('permissions') && is_array($this->get('permissions'))) {
            $this->merge([
                'permissions' => array_unique(array_filter($this->get('permissions'))),
            ]);
        }
    }

    /**
     * Get the sanitized and validated role data.
     *
     * @return array<string, mixed>
     */
    public function getRoleData(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ];
    }

    /**
     * Get the validated permissions or empty array.
     *
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        $validated = $this->validated();
        $permissions = isset($validated['permissions']) && is_array($validated['permissions']) ? $validated['permissions'] : [];

        // Ensure all values are strings
        return array_values(array_filter($permissions, 'is_string'));
    }
}
