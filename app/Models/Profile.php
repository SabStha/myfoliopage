<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Profile extends Model
{
    protected $fillable = ['user_id', 'name','role','photo_path'];

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
