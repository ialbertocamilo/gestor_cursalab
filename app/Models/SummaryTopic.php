<?php

namespace App\Models;

class SummaryTopic extends BaseModel
{
    protected $table = 'summary_topics';

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
