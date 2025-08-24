<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mews\Purifier\Facades\Purifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class XSSSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function html_purifier_prevents_script_injection(): void
    {
        $maliciousInput = '<script>alert("XSS")</script><p>Safe content</p>';
        $cleaned = Purifier::clean($maliciousInput, 'docs');

        $this->assertIsString($cleaned);
        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringNotContainsString('alert', $cleaned);
        $this->assertStringContainsString('<p>Safe content</p>', $cleaned);
    }

    #[Test]
    public function html_purifier_prevents_iframe_injection(): void
    {
        $maliciousInput = '<iframe src="javascript:alert(\'XSS\')"></iframe><p>Safe content</p>';
        $cleaned = Purifier::clean($maliciousInput, 'docs');

        $this->assertIsString($cleaned);
        $this->assertStringNotContainsString('<iframe>', $cleaned);
        $this->assertStringNotContainsString('javascript:', $cleaned);
        $this->assertStringContainsString('<p>Safe content</p>', $cleaned);
    }

    #[Test]
    public function html_purifier_allows_safe_markdown_content(): void
    {
        $markdownContent = '<h1>Title</h1><p>Paragraph</p><code>code</code><pre class="language-php">$var = "value";</pre>';
        $cleaned = Purifier::clean($markdownContent, 'docs');

        $this->assertIsString($cleaned);
        $this->assertStringContainsString('<h1>Title</h1>', $cleaned);
        $this->assertStringContainsString('<p>Paragraph</p>', $cleaned);
        $this->assertStringContainsString('<code>code</code>', $cleaned);
        $this->assertStringContainsString('<pre class="language-php">', $cleaned);
    }

    #[Test]
    public function html_purifier_prevents_onclick_attributes(): void
    {
        $maliciousInput = '<a href="https://example.com" onclick="alert(\'XSS\')">Link</a>';
        $cleaned = Purifier::clean($maliciousInput, 'docs');

        $this->assertIsString($cleaned);
        $this->assertStringNotContainsString('onclick', $cleaned);
        $this->assertStringContainsString('<a href="https://example.com">Link</a>', $cleaned);
    }

    #[Test]
    public function html_purifier_preserves_table_structure(): void
    {
        $tableContent = '<table><thead><tr><th>Header</th></tr></thead><tbody><tr><td>Data</td></tr></tbody></table>';
        $cleaned = Purifier::clean($tableContent, 'docs');

        $this->assertIsString($cleaned);
        $this->assertStringContainsString('<table>', $cleaned);
        $this->assertStringContainsString('<thead>', $cleaned);
        $this->assertStringContainsString('<tbody>', $cleaned);
        $this->assertStringContainsString('<th>Header</th>', $cleaned);
        $this->assertStringContainsString('<td>Data</td>', $cleaned);
    }

    #[Test]
    public function html_purifier_preserves_mark_elements_for_highlighting(): void
    {
        $highlightedContent = '<p>This is <mark class="bg-yellow-200">highlighted</mark> text</p>';
        $cleaned = Purifier::clean($highlightedContent, 'docs');

        $this->assertIsString($cleaned);
        $this->assertStringContainsString('<mark class="bg-yellow-200">highlighted</mark>', $cleaned);
    }
}
