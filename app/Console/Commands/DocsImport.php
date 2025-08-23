<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Documentation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class DocsImport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'docs:import 
                            {--fresh : Clear existing documentation before importing}
                            {--path= : Custom path to docs directory}';

    /**
     * The console command description.
     */
    protected $description = 'Import markdown documentation files into searchable database';

    private MarkdownConverter $markdownConverter;

    private int $importedCount = 0;

    private int $errorCount = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->setupMarkdownConverter();

        $this->components->info('🚀 Dokumentáció importálása...');

        if ($this->option('fresh')) {
            $this->clearExistingDocumentation();
        }

        $pathOption = $this->option('path');
        $configPath = config('docs.path');
        $docsPath = $pathOption ?: ($configPath ?: base_path('docs'));
        $docsPath = is_string($docsPath) ? $docsPath : base_path('docs');

        if (!File::exists($docsPath)) {
            $this->components->error("📁 Docs mappa nem található: {$docsPath}");

            return self::FAILURE;
        }

        $this->components->info("📁 {$docsPath} mappa szkennelése...");

        $markdownFiles = $this->findMarkdownFiles($docsPath);

        if (empty($markdownFiles)) {
            $this->components->warn('📄 Nem található markdown fájl');

            return self::SUCCESS;
        }

        $fileCount = count($markdownFiles);
        $this->components->info("📄 {$fileCount} markdown fájl találva");

        $progressBar = $this->output->createProgressBar(count($markdownFiles));
        $progressBar->setFormat('📊 Importálás: %bar% %current%/%max% (%percent:3s%%) %message%');

        foreach ($markdownFiles as $file) {
            $fileName = basename($file);
            $progressBar->setMessage('Feldolgozás: ' . $fileName);

            try {
                $this->importMarkdownFile($file, $docsPath);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->error("⚠️ Hiba a {$fileName} feldolgozásakor: {$e->getMessage()}");
                $this->errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Index documents in Scout/Meilisearch
        if (config('scout.driver') !== null) {
            $this->components->info('🔍 Dokumentumok indexelése Meilisearch-ben...');

            try {
                Documentation::makeAllSearchable();
                $this->components->info('✅ Indexelés befejezve');
            } catch (\Exception $e) {
                $this->components->error("❌ Indexelési hiba: {$e->getMessage()}");
            }
        } else {
            $this->components->info('🔍 Scout driver letiltva, indexelés kihagyva');
        }

        // Summary
        $this->displaySummary();

        return $this->errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Setup CommonMark converter with GitHub flavored markdown.
     */
    private function setupMarkdownConverter(): void
    {
        // Create environment with GitHub Flavored Markdown support
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // Add CommonMark core extension
        $environment->addExtension(new CommonMarkCoreExtension());

        // Add GitHub Flavored Markdown extension (includes table support)
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $this->markdownConverter = new MarkdownConverter($environment);
    }

    /**
     * Clear existing documentation.
     */
    private function clearExistingDocumentation(): void
    {
        $this->components->info('⚡ Meglévő dokumentumok törlése...');

        // Remove from search index first
        Documentation::removeAllFromSearch();

        // Then delete from database
        Documentation::query()->delete();

        $this->components->info('✅ Meglévő dokumentumok törölve');
    }

    /**
     * Find all markdown files recursively.
     */
    /**
     * @return array<string>
     */
    private function findMarkdownFiles(string $path): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Import a single markdown file.
     */
    private function importMarkdownFile(string $filePath, string $basePath): void
    {
        $content = File::get($filePath);
        $relativePath = str_replace($basePath . '/', '', $filePath);

        // Extract title from first heading or filename
        $title = $this->extractTitle($content, $filePath);

        // Determine category from path
        $category = $this->extractCategory($relativePath);

        // Generate slug
        $slug = Str::slug($title);

        // Convert markdown to HTML
        $htmlContent = $this->markdownConverter->convert($content)->getContent();

        // Extract excerpt from first paragraph
        $excerpt = $this->extractExcerpt($content);

        // Create or update documentation
        Documentation::updateOrCreate(
            ['path' => $relativePath],
            [
                'title' => $title,
                'content' => $htmlContent,
                'category' => $category,
                'slug' => $this->ensureUniqueSlug($slug, $category),
                'excerpt' => $excerpt,
            ],
        );
    }

    /**
     * Extract title from markdown content.
     */
    private function extractTitle(string $content, string $filePath): string
    {
        // Try to find first # heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        // Fallback to filename
        return Str::title(
            str_replace(
                ['-', '_'],
                ' ',
                pathinfo($filePath, PATHINFO_FILENAME),
            ),
        );
    }

    /**
     * Extract category from file path.
     */
    private function extractCategory(string $relativePath): string
    {
        $pathParts = explode('/', $relativePath);

        // If file is in subdirectory, use directory name as category
        if (count($pathParts) > 1) {
            return $pathParts[0];
        }

        // Default category for root files
        return 'general';
    }

    /**
     * Extract excerpt from markdown content.
     */
    private function extractExcerpt(string $content): string
    {
        // Remove headers and get first paragraph
        $lines = explode("\n", $content);
        $excerpt = '';

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and headers
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Skip code blocks
            if (str_starts_with($line, '```')) {
                break;
            }

            $excerpt = $line;
            break;
        }

        // Strip markdown formatting and limit length
        $plainText = strip_tags($excerpt);

        return strlen($plainText) > 200
            ? substr($plainText, 0, 200) . '...'
            : $plainText;
    }

    /**
     * Ensure slug is unique within category.
     */
    private function ensureUniqueSlug(string $slug, string $category): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (Documentation::where('category', $category)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Display import summary.
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->components->info('📊 Importálás befejezve');
        $this->components->info("✅ {$this->importedCount} dokumentum sikeresen importálva");

        if ($this->errorCount > 0) {
            $this->components->error("❌ {$this->errorCount} hiba történt");
        }

        // Display categories
        $categories = Documentation::select('category')
            ->groupBy('category')
            ->pluck('category')
            ->toArray();

        if (!empty($categories)) {
            $this->components->info('📁 Kategóriák: ' . implode(', ', $categories));
        }

        $this->components->info('🔍 Keresés elérhető: php artisan tinker -> Documentation::search("keresési kifejezés")->get()');
    }
}
