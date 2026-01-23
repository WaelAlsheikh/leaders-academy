<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $guard = 'student';

    protected $fillable = [
        'first_name',
        'last_name',
        'first_name_en',
        'username',
        'email',
        'phone',
        'is_active',
        'password',
        'acceptance_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

/**
 * عرض جميع الطلاب (افتراضي)
 */
public function scopeAllStudents($query)
{
    return $query;
}

/**
 * الطلاب النشطون
 */
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

/**
 * الطلاب غير النشطين
 */
public function scopeInactive($query)
{
    return $query->where('is_active', false);
}

}

