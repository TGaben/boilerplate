<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Documentation extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentationFactory> */
    use HasFactory;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'content',
        'category',
        'path',
        'slug',
        'excerpt',
    ];

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'docs_index';
    }

    /**
     * Get the indexable data array for the model.
     */
    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => strip_tags($this->content),
            'category' => $this->category,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'path' => $this->path,
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): string
    {
        return 'id';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return !empty($this->title) && !empty($this->content);
    }

    /**
     * Get documents by category.
     */
    /**
     * @param \Illuminate\Database\Eloquent\Builder<Documentation> $query
     *
     * @return \Illuminate\Database\Eloquent\Builder<Documentation>
     */
    public function scopeByCategory($query, string $category): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Get the URL for this documentation page.
     */
    public function getUrlAttribute(): string
    {
        try {
            return route('docs.show', [
                'category' => $this->category,
                'slug' => $this->slug,
            ]);
        } catch (\Exception $e) {
            // Fallback URL if route doesn't exist
            return "/docs/{$this->category}/{$this->slug}";
        }
    }

    /**
     * Get a short excerpt from content if not provided.
     */
    public function getExcerptAttribute(): string
    {
        if (!empty($this->attributes['excerpt']) && is_string($this->attributes['excerpt'])) {
            return $this->attributes['excerpt'];
        }

        $plainText = strip_tags((string) $this->content);

        return strlen($plainText) > 200
            ? substr($plainText, 0, 200) . '...'
            : $plainText;
    }
}
