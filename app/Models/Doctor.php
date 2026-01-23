<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctor extends Authenticatable
{
    protected $table = 'doctors';

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'academic_degree',
        'specialization',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];
}
