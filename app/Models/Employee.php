<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Domain\Email\Concerns\HasInstitutionalMail;

class Employee extends Authenticatable
{
    use Notifiable;
    use HasInstitutionalMail;

    protected $table = 'employees';
    protected $guard = 'employee';

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'institutional_email',
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
