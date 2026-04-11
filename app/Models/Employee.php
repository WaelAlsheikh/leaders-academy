<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    protected $table = 'employees';
    protected $guard = 'employee';

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'job_title',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
