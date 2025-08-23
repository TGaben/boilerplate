<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\Documentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocsImportCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable Scout for testing to prevent real Meilisearch calls
        config(['scout.driver' => null]);
    }

    #[Test]
    public function docs_import_command_processes_markdown_files(): void
    {
        // Create test markdown files
        $this->createTestMarkdownFiles();

        $testPath = storage_path('framework/testing/docs');

        // Verify test files exist
        $this->assertTrue(File::exists("{$testPath}/core/authentication.md"));
        $this->assertTrue(File::exists("{$testPath}/recipes/api-development.md"));

        // Create and execute the command instance directly
        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        // Mock the command's input/output for testing
        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--path' => $testPath,
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        // Execute the command
        $exitCode = $command->run($input, $output);

        $this->assertEquals(0, $exitCode);

        // Verify documents were created
        $count = Documentation::count();
        $this->assertGreaterThan(0, $count, 'No documents were imported. Database is empty.');

        $coreDoc = Documentation::where('category', 'core')->first();
        $this->assertNotNull($coreDoc, 'No core document found');

        if ($coreDoc) {
            $this->assertEquals('Test Core Document', $coreDoc->title);
        }

        $recipeDoc = Documentation::where('category', 'recipes')->first();
        $this->assertNotNull($recipeDoc, 'No recipe document found');

        if ($recipeDoc) {
            $this->assertEquals('Test Recipe Document', $recipeDoc->title);
        }
    }

    #[Test]
    public function docs_import_command_with_fresh_flag_clears_existing_docs(): void
    {
        // Create existing documentation
        Documentation::factory()->create(['title' => 'Existing Doc']);

        $this->createTestMarkdownFiles();

        $testPath = storage_path('framework/testing/docs');

        // Create and execute the command instance directly
        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--fresh' => true,
            '--path' => $testPath,
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $exitCode = $command->run($input, $output);
        $this->assertEquals(0, $exitCode);

        // Verify old docs were removed
        $this->assertDatabaseMissing('documentations', [
            'title' => 'Existing Doc',
        ]);

        // Verify new docs were added
        $this->assertDatabaseHas('documentations', [
            'title' => 'Test Core Document',
        ]);
    }

    #[Test]
    public function docs_import_command_handles_nonexistent_directory(): void
    {
        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--path' => '/nonexistent/path',
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $exitCode = $command->run($input, $output);
        $this->assertEquals(1, $exitCode);
    }

    #[Test]
    public function docs_import_command_handles_empty_directory(): void
    {
        // Create empty directory
        $emptyPath = storage_path('framework/testing/empty_docs');
        File::ensureDirectoryExists($emptyPath);

        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--path' => $emptyPath,
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $exitCode = $command->run($input, $output);
        $this->assertEquals(0, $exitCode);

        // Cleanup
        File::deleteDirectory($emptyPath);
    }

    #[Test]
    public function docs_import_command_extracts_title_from_heading(): void
    {
        $this->createTestMarkdownFiles();

        $testPath = storage_path('framework/testing/docs');

        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--path' => $testPath,
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $exitCode = $command->run($input, $output);
        $this->assertEquals(0, $exitCode);

        // Check that title was extracted from # heading
        $doc = Documentation::where('category', 'core')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('Test Core Document', $doc->title);
    }

    #[Test]
    public function docs_import_command_categorizes_by_directory(): void
    {
        $this->createTestMarkdownFiles();

        $testPath = storage_path('framework/testing/docs');

        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--path' => $testPath,
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $exitCode = $command->run($input, $output);
        $this->assertEquals(0, $exitCode);

        $this->assertEquals(1, Documentation::where('category', 'core')->count());
        $this->assertEquals(1, Documentation::where('category', 'recipes')->count());
    }

    #[Test]
    public function docs_import_command_generates_unique_slugs(): void
    {
        // Create files with same title in different categories
        File::ensureDirectoryExists(storage_path('framework/testing/docs/core'));
        File::ensureDirectoryExists(storage_path('framework/testing/docs/recipes'));

        File::put(
            storage_path('framework/testing/docs/core/same-title.md'),
            "# Same Title\n\nCore content",
        );

        File::put(
            storage_path('framework/testing/docs/recipes/same-title.md'),
            "# Same Title\n\nRecipe content",
        );

        $testPath = storage_path('framework/testing/docs');

        $command = new \App\Console\Commands\DocsImport();
        $command->setLaravel($this->app);

        $input = new \Symfony\Component\Console\Input\ArrayInput([
            '--path' => $testPath,
        ]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        $exitCode = $command->run($input, $output);
        $this->assertEquals(0, $exitCode);

        $docs = Documentation::where('title', 'Same Title')->get();
        $this->assertCount(2, $docs);

        // Both should have the same slug since they're in different categories
        $coreDoc = $docs->where('category', 'core')->first();
        $recipeDoc = $docs->where('category', 'recipes')->first();

        $this->assertNotNull($coreDoc);
        $this->assertNotNull($recipeDoc);
        $this->assertEquals('same-title', $coreDoc->slug);
        $this->assertEquals('same-title', $recipeDoc->slug);
    }

    private function createTestMarkdownFiles(): void
    {
        // Create test docs directory structure
        File::ensureDirectoryExists(storage_path('framework/testing/docs/core'));
        File::ensureDirectoryExists(storage_path('framework/testing/docs/recipes'));

        // Core document
        File::put(
            storage_path('framework/testing/docs/core/authentication.md'),
            "# Test Core Document\n\nThis is a core authentication document.\n\n## Features\n\n- Login system\n- User management",
        );

        // Recipe document
        File::put(
            storage_path('framework/testing/docs/recipes/api-development.md'),
            "# Test Recipe Document\n\nThis is an API development recipe.\n\n```php\nRoute::get('/api/test', function() {\n    return response()->json(['status' => 'ok']);\n});\n```",
        );
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists(storage_path('framework/testing/docs'))) {
            File::deleteDirectory(storage_path('framework/testing/docs'));
        }

        parent::tearDown();
    }
}
