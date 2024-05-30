<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class Internship extends BaseModel
{
    protected $table = 'internships';

    protected $fillable = [
        'title',
        'leaders',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }
}
