<?php

namespace App\Models;

class ProcessSummaryStage extends BaseModel
{
    protected $table = 'process_summary_users_stages';

    protected $fillable = [
        'user_id', 'stage_id', 'status_id', 'progress'
    ];

    public $defaultRelationships = [
        'stage_id' => 'stage',
        'user_id' => 'user'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

}
