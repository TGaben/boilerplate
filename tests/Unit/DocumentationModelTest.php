<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Documentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocumentationModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function documentation_model_has_searchable_array(): void
    {
        $doc = new Documentation([
            'title' => 'Test Title',
            'content' => 'Test content with keywords',
            'category' => 'core',
            'path' => 'docs/core/test.md',
            'slug' => 'test-title',
        ]);

        $searchable = $doc->toSearchableArray();

        $this->assertArrayHasKey('title', $searchable);
        $this->assertArrayHasKey('content', $searchable);
        $this->assertArrayHasKey('category', $searchable);
        $this->assertEquals('Test Title', $searchable['title']);
        $this->assertEquals('core', $searchable['category']);
    }

    #[Test]
    public function documentation_model_generates_search_key(): void
    {
        $doc = Documentation::factory()->create([
            'slug' => 'test-document',
        ]);

        $this->assertEquals($doc->id, $doc->getScoutKey());
    }

    #[Test]
    public function documentation_model_has_proper_fillable_attributes(): void
    {
        $fillable = (new Documentation())->getFillable();

        $expectedFillable = [
            'title', 'content', 'category', 'path', 'slug', 'excerpt',
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    #[Test]
    public function documentation_model_scope_by_category_works(): void
    {
        Documentation::factory()->create(['category' => 'core']);
        Documentation::factory()->create(['category' => 'recipes']);

        $coreDocuments = Documentation::byCategory('core')->get();
        $this->assertCount(1, $coreDocuments);
        $firstDoc = $coreDocuments->first();
        $this->assertInstanceOf(Documentation::class, $firstDoc);
        $this->assertEquals('core', $firstDoc->category);
    }

    #[Test]
    public function documentation_model_generates_url_attribute(): void
    {
        $doc = Documentation::factory()->create([
            'category' => 'core',
            'slug' => 'test-document',
        ]);

        $expectedUrl = route('docs.show', [
            'category' => 'core',
            'slug' => 'test-document',
        ]);

        $this->assertEquals($expectedUrl, $doc->url);
    }

    #[Test]
    public function documentation_model_generates_excerpt_from_content(): void
    {
        $longContent = str_repeat('This is a very long content. ', 20);

        $doc = Documentation::factory()->create([
            'content' => $longContent,
            'excerpt' => null,
        ]);

        $excerpt = $doc->excerpt;
        $this->assertLessThanOrEqual(203, strlen($excerpt)); // 200 chars + "..."
        $this->assertStringContainsString('...', $excerpt);
    }

    #[Test]
    public function documentation_model_should_be_searchable_when_has_title_and_content(): void
    {
        $doc = new Documentation([
            'title' => 'Test Title',
            'content' => 'Test content',
        ]);

        $this->assertTrue($doc->shouldBeSearchable());
    }

    #[Test]
    public function documentation_model_should_not_be_searchable_when_missing_title_or_content(): void
    {
        $docWithoutTitle = new Documentation([
            'title' => '',
            'content' => 'Test content',
        ]);

        $docWithoutContent = new Documentation([
            'title' => 'Test Title',
            'content' => '',
        ]);

        $this->assertFalse($docWithoutTitle->shouldBeSearchable());
        $this->assertFalse($docWithoutContent->shouldBeSearchable());
    }
}
