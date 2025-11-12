<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BookPage extends Model
{
    use HasTranslations;
    protected $fillable = [
        'user_id', 'title', 'slug', 'content', 'summary', 'author', 'book_title', 'page_number', 'read_at',
        'key_objectives', 'reflection', 'applied_snippet', 'references',
        'how_to_run', 'result_evidence', 'difficulty', 'time_spent', 'status',
    ];

    protected $casts = [
        'read_at' => 'date',
        'page_number' => 'integer',
        'time_spent' => 'integer',
        // Cast translatable fields as JSON
        'title' => 'array',
        'content' => 'array',
        'summary' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'content',
            'summary',
        ];
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function sections(): MorphToMany
    {
        // Sections are CategoryItems
        return $this->morphToMany(CategoryItem::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
