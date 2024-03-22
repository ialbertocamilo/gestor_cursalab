<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Course;
use DB;

class ProcessSummaryUser extends BaseModel
{
    protected $table = 'process_summary_users';

    protected $fillable = [
        'user_id', 'process_id', 'status_id', 'absences', 'progress', 'first_entry', 'completed_instruction'
    ];

    // protected $casts = [
    //     'answers' => 'array',
    // ];
    protected $casts = [
        'completed_instruction' => 'boolean'
    ];

    public $defaultRelationships = [
        'process_id' => 'activity',
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

}
