<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineEntry extends Model
{
    protected $fillable = ['title', 'occurred_at', 'description'];
}
