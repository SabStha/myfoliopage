<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomePageSection extends Model
{
    use HasTranslations;
    protected $fillable = [
        'user_id', 'nav_item_id',
        'position',
        'text_alignment', // 'left' or 'right'
        'animation_style', // 'grid_editorial_collage', 'list_alternating_cards', 'carousel_scroll_left', 'carousel_scroll_right'
        'title',
        'subtitle',
        'enabled',
        'selected_nav_link_ids', // JSON array of NavLink IDs to display
        'subsection_configurations', // JSON object mapping nav_link_id to { animation_style, layout_style }
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'position' => 'integer',
        'selected_nav_link_ids' => 'array',
        'subsection_configurations' => 'array',
        // Cast translatable fields as JSON
        'title' => 'array',
        'subtitle' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'subtitle',
        ];
    }

    public function navItem(): BelongsTo
    {
        return $this->belongsTo(NavItem::class);
    }
}

