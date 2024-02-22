<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Course;
use DB;

class ProcessUserAttendance extends BaseModel
{
    protected $table = 'process_user_attendance';

    protected $fillable = [
        'user_id', 'process_id', 'activity_id', 'status_id', 'instructor_id', 'registered', 'justification'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class,'activity_id');
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

}
