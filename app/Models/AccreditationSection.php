<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccreditationSection extends Model
{
    protected $fillable = [
        'title',
        'short_description',
        'icons',
        'order'
    ];

    // cast icons JSON => array تلقائياً
    protected $casts = [
        'icons' => 'array',
    ];
}
