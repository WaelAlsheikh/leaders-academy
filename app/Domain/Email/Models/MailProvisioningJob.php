<?php

namespace App\Domain\Email\Models;

use Illuminate\Database\Eloquent\Model;

class MailProvisioningJob extends Model
{
    protected $fillable = [
        'type',
        'payload',
        'status',
        'attempts',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
    ];
}
