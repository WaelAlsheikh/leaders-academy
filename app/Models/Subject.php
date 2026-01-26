<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'college_id',
        'name',
        'code',
        'credit_hours',
        'is_active',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }
}
