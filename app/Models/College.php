<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class College extends Model
{
    protected $fillable = [
        'title',
        'code',
        'slug',
        'short_description',
        'long_description',
        'image',
        'price_per_credit_hour',
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

        static::deleted(function ($model) {
            RegistrableEntity::query()
                ->where('entity_type', 'college')
                ->where('entity_id', $model->id)
                ->delete();
        });
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function registrableEntity()
    {
        return $this->hasOne(RegistrableEntity::class, 'entity_id', 'id')
            ->where('entity_type', 'college');
    }

    public function studyYears()
    {
        return $this->hasManyThrough(
            StudyYear::class,
            RegistrableEntity::class,
            'entity_id',
            'registrable_entity_id',
            'id',
            'id'
        )->where('registrable_entities.entity_type', 'college');
    }

    public function enrollmentCycles()
    {
        return $this->hasMany(EnrollmentCycle::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}
