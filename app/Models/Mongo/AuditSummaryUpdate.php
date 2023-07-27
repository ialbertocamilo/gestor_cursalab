<?php

namespace App\Models\Mongo;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;

use Carbon\Carbon;

class AuditSummaryUpdate extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'audit_summary_updates';

    protected $fillable = ['user_ids', 'course_ids', 'summary_course_update', 'type'];
  
    // protected $casts = [
    //     'created_at' => 'datetime',
    // ];
}
