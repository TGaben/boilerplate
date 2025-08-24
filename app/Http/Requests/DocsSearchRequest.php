<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocsSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Documentation search is public
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\_\.\,\!\?\:\;\(\)\[\]]*$/', // Only safe characters
            ],
            'category' => [
                'sometimes',
                'string',
                Rule::in(['core', 'recipes', 'deployment', 'troubleshooting', 'general']),
            ],
            'limit' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
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
            'q.required' => 'A keresési kifejezés kötelező.',
            'q.min' => 'A keresési kifejezésnek legalább 2 karakter hosszúnak kell lennie.',
            'q.max' => 'A keresési kifejezés maximum 100 karakter hosszú lehet.',
            'q.regex' => 'A keresési kifejezés érvénytelen karaktereket tartalmaz.',
            'category.in' => 'A kategória értéke érvénytelen.',
            'limit.integer' => 'A limit értéknek egész számnak kell lennie.',
            'limit.min' => 'A limit minimum 1 lehet.',
            'limit.max' => 'A limit maximum 50 lehet.',
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
            'q' => 'keresési kifejezés',
            'category' => 'kategória',
            'limit' => 'találatok száma',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize and normalize the search query
        if ($this->has('q')) {
            $this->merge([
                'q' => trim(strip_tags($this->string('q')->value())),
            ]);
        }

        // Ensure limit is integer if provided
        if ($this->has('limit')) {
            $limitValue = $this->get('limit', 10);
            $this->merge([
                'limit' => is_numeric($limitValue) ? (int) $limitValue : 10,
            ]);
        }
    }

    /**
     * Get the sanitized and validated search query.
     */
    public function getSearchQuery(): string
    {
        $validated = $this->validated();

        return is_string($validated['q']) ? $validated['q'] : '';
    }

    /**
     * Get the validated category or null.
     */
    public function getCategory(): ?string
    {
        $validated = $this->validated();

        return isset($validated['category']) && is_string($validated['category']) ? $validated['category'] : null;
    }

    /**
     * Get the validated limit.
     */
    public function getLimit(): int
    {
        $validated = $this->validated();

        return isset($validated['limit']) && is_int($validated['limit']) ? $validated['limit'] : 10;
    }
}
