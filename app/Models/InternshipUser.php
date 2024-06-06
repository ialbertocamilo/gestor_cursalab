<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class InternshipUser extends BaseModel
{
    protected $table = 'internship_users';

    protected $fillable = [
        'user_id',
        'internship_id',
        'leader_id',
        'meeting_date_1',
        'meeting_date_2',
        'meeting_time_1',
        'meeting_time_2',
        'status_id',
        'active',
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

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }
}
