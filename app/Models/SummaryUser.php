<?php

namespace App\Models;

class SummaryUser extends Summary
{
    protected $table = 'summary_users';

    protected $fillable = [
        'last_time_evaluated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

}
