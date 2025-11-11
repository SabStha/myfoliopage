<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lab extends Model
{
    protected $fillable = [
        'title', 'slug', 'platform', 'room_url', 'completed_at', 'summary',
    ];

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
