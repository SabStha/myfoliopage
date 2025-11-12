<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OngoingProgressItem extends Model
{
    protected $fillable = [
        'label',
        'unit',
        'value',
        'goal',
        'link',
        'eta',
        'trend_amount',
        'trend_window',
        'order',
    ];

    protected $casts = [
        'value' => 'integer',
        'goal' => 'integer',
        'order' => 'integer',
        'trend_amount' => 'integer',
    ];
}








