<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrainingProgramBranch extends Model
{
    protected $fillable = [
        'training_program_id',
        'title',
        'slug',
        'short_description',
        'long_description',
        'image',
        'price_per_credit_hour',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_credit_hour' => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . uniqid();
            }
        });

        static::deleted(function (self $model): void {
            RegistrableEntity::query()
                ->where('entity_type', 'training_program_branch')
                ->where('entity_id', $model->id)
                ->delete();
        });
    }
}
