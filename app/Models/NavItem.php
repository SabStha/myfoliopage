<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavItem extends Model
{
    use HasTranslations;
    
    protected $fillable = [
        'label', 'route', 'url', 'active_pattern', 'icon_svg', 'position', 'visible',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'position' => 'integer',
        'label' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return ['label'];
    }

    public function links(): HasMany { return $this->hasMany(NavLink::class)->orderBy('position'); }
}
