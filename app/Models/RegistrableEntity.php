<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class RegistrableEntity extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'title_snapshot',
        'price_per_credit_hour',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_credit_hour' => 'decimal:2',
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(RegistrableSubject::class);
    }

    public function enrollmentCycles(): HasMany
    {
        return $this->hasMany(EnrollmentCycle::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return (string) ($this->title_snapshot ?? ($this->entity?->title ?? 'غير معروف'));
    }

    public function getEntityAttribute(): mixed
    {
        return match ($this->entity_type) {
            'college' => College::find($this->entity_id),
            'program_branch' => ProgramBranch::find($this->entity_id),
            'training_program_branch' => TrainingProgramBranch::find($this->entity_id),
            default => null,
        };
    }

    public static function syncFromSources(): void
    {
        if (Schema::hasTable('colleges')) {
            College::query()->select('id', 'title', 'price_per_credit_hour')->chunk(200, function ($items): void {
                foreach ($items as $item) {
                    self::updateOrCreate(
                        ['entity_type' => 'college', 'entity_id' => $item->id],
                        [
                            'title_snapshot' => $item->title,
                            'price_per_credit_hour' => $item->price_per_credit_hour ?? 0,
                            'is_active' => true,
                        ]
                    );
                }
            });
        }

        if (Schema::hasTable('program_branches')) {
            ProgramBranch::query()->select('id', 'title', 'is_active', 'price_per_credit_hour')->chunk(200, function ($items): void {
                foreach ($items as $item) {
                    self::updateOrCreate(
                        ['entity_type' => 'program_branch', 'entity_id' => $item->id],
                        [
                            'title_snapshot' => $item->title,
                            'price_per_credit_hour' => $item->price_per_credit_hour ?? 0,
                            'is_active' => (bool) ($item->is_active ?? true),
                        ]
                    );
                }
            });
        }

        if (Schema::hasTable('training_program_branches')) {
            TrainingProgramBranch::query()->select('id', 'title', 'is_active', 'price_per_credit_hour')->chunk(200, function ($items): void {
                foreach ($items as $item) {
                    self::updateOrCreate(
                        ['entity_type' => 'training_program_branch', 'entity_id' => $item->id],
                        [
                            'title_snapshot' => $item->title,
                            'price_per_credit_hour' => $item->price_per_credit_hour ?? 0,
                            'is_active' => (bool) ($item->is_active ?? true),
                        ]
                    );
                }
            });
        }
    }
}
