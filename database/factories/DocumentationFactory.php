<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documentation>
 */
class DocumentationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $categories = ['core', 'recipes', 'deployment', 'troubleshooting'];

        return [
            'title' => $title,
            'content' => $this->generateMarkdownContent(),
            'category' => $this->faker->randomElement($categories),
            'path' => 'docs/' . strtolower(str_replace(' ', '-', $title)) . '.md',
            'slug' => \Illuminate\Support\Str::slug($title),
            'excerpt' => $this->faker->paragraph(2),
        ];
    }

    /**
     * Generate realistic markdown content for documentation.
     */
    private function generateMarkdownContent(): string
    {
        $sections = [
            '# ' . $this->faker->sentence(4),
            '',
            $this->faker->paragraph(3),
            '',
            '## Features',
            '',
            '- ' . $this->faker->sentence(3),
            '- ' . $this->faker->sentence(4),
            '- ' . $this->faker->sentence(2),
            '',
            '## Installation',
            '',
            '```bash',
            'composer require ' . $this->faker->word . '/' . $this->faker->word,
            '```',
            '',
            $this->faker->paragraph(2),
            '',
            '## Usage Example',
            '',
            '```php',
            '<?php',
            '',
            '$' . $this->faker->word . ' = new ' . $this->faker->word . '();',
            '$result = $' . $this->faker->word . '->' . $this->faker->word . '();',
            '```',
            '',
            '> **Note:** ' . $this->faker->sentence(5),
        ];

        return implode("\n", $sections);
    }

    /**
     * Create a core documentation.
     */
    public function core(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'core',
        ]);
    }

    /**
     * Create a recipe documentation.
     */
    public function recipe(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'recipes',
        ]);
    }

    /**
     * Create a deployment documentation.
     */
    public function deployment(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'deployment',
        ]);
    }
}
