<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    protected $fillable = [
        'creation_mode',
        'pass_percentage',
    ];

    protected $casts = [
        'pass_percentage' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'creation_mode' => 'random',
            'pass_percentage' => (int) config('exams.default_pass_percentage', 60),
        ]);
    }

    public static function creationMode(): string
    {
        return static::current()->creation_mode;
    }

    public static function isRandomMode(): bool
    {
        return static::creationMode() === 'random';
    }

    public static function isManualMode(): bool
    {
        return static::creationMode() === 'manual';
    }

    public static function passPercentage(): int
    {
        $value = (int) (static::current()->pass_percentage
            ?? config('exams.default_pass_percentage', 60));

        return max(0, min(100, $value));
    }
}
