<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentPlatform extends Model
{
    protected $fillable = [
        'title', 'slug', 
        // الحقول الجديدة للقسمين
        'title1','image1','content1',
        'title2','image2','content2',
    ];

    public static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->slug) && !empty($item->title)) {
                $item->slug = Str::slug($item->title);
            }
        });
    }
}
