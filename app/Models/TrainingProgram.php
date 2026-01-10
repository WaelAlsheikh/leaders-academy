<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TrainingProgram extends Model
{
    protected $table = 'training_programs';

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'long_description',
        'category',
        'duration',
        'certificate',
        'image',
    ];

    // استخدم slug في روابط الـ route model binding
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // مساعدة لعمل slug تلقائي (اختياري)
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . uniqid();
            }
        });
    }
}
