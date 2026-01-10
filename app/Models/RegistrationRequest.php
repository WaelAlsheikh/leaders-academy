<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    use HasFactory;

    protected $table = 'registration_requests';

    protected $fillable = [
        'program_type',
        'program_id',
        'program_title',
        'name',
        'phone',
        'email',
        'notes',
        'source',
        'status',
        'meta',
    ];

    // cast meta to array
    protected $casts = [
        'meta' => 'array',
    ];
}
