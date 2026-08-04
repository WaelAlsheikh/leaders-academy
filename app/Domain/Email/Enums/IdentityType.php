<?php

namespace App\Domain\Email\Enums;

enum IdentityType: string
{
    case Student = 'student';
    case Doctor = 'doctor';
    case Employee = 'employee';
    case Admin = 'admin';
    case System = 'system';

    public function suffix(): string
    {
        return (string) config('email_module.identity_suffixes.'.$this->value, $this->value);
    }

    public function defaultQuotaMb(): int
    {
        return (int) config('email_module.default_quotas_mb.'.$this->value, 1024);
    }

    public function maxAliases(): int
    {
        return (int) config('email_module.max_aliases.'.$this->value, 5);
    }

    public function label(): string
    {
        return match ($this) {
            self::Student => 'طالب',
            self::Doctor => 'دكتور',
            self::Employee => 'موظف',
            self::Admin => 'مسؤول',
            self::System => 'نظام',
        };
    }

    public static function fromModel(object $model): self
    {
        return match (true) {
            $model instanceof \App\Models\Student => self::Student,
            $model instanceof \App\Models\Doctor => self::Doctor,
            $model instanceof \App\Models\Employee => self::Employee,
            $model instanceof \App\Models\User => self::Admin,
            default => throw new \InvalidArgumentException('Unsupported identity model: '.get_class($model)),
        };
    }
}
