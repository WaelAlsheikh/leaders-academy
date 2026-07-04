<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    protected $fillable = [
        'creation_mode',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'creation_mode' => 'random',
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
}
