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
        'password',
        'acceptance_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
