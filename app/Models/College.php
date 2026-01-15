<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class College extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'long_description',
        'image',
        'price',
    ];

    // إذا لم يتم تمرير slug نحوله تلقائياً من title (اختياري)
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug) && !empty($model->title)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }
}
