<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Course;
use DB;

class ProcessSummaryActivity extends BaseModel
{
    protected $table = 'process_summary_users_activities';

    protected $fillable = [
        'user_id', 'activity_id', 'status_id',
    ];

    // protected $casts = [
    //     'answers' => 'array',
    // ];

    public $defaultRelationships = [
        'activity_id' => 'activity',
        'user_id' => 'user'
    ];

    // protected $dates = [
    //     'current_quiz_started_at',
    //     'current_quiz_finishes_at',
    //     'last_time_evaluated_at',
    // ];

    // protected $hidden = ['answers'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

}
