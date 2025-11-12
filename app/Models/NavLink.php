<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NavLink extends Model
{
    use HasTranslations;
    
    protected $fillable = [
        'user_id', 'nav_item_id','title','url','proof_url','progress','issued_at','notes','position','category_id','image_path','document_path'
    ];

    protected $casts = [
        'progress' => 'integer',
        'position' => 'integer',
        'issued_at' => 'date',
        'title' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
        ];
    }

    public function navItem(): BelongsTo { return $this->belongsTo(NavItem::class); }
    
    // DEPRECATED: Single category - kept for backward compatibility
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    
    // NEW: Multiple categories relationship
    public function categories(): BelongsToMany { 
        return $this->belongsToMany(Category::class)->withTimestamps(); 
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? asset('storage/'.$this->document_path) : null;
    }
}
