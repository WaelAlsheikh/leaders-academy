<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','slug','short_description','long_description','duration','certificate','image'
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ProgramBranch::class)->orderBy('order');
    }
}
