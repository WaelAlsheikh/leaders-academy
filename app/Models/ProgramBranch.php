<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ProgramBranch extends Model
{
    protected $fillable = [
        'program_id',
        'title',
        'code',
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

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function registrableEntity(): HasOne
    {
        return $this->hasOne(RegistrableEntity::class, 'entity_id', 'id')
            ->where('entity_type', 'program_branch');
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
                ->where('entity_type', 'program_branch')
                ->where('entity_id', $model->id)
                ->delete();
        });
    }
}
