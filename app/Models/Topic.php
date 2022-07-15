<?php

namespace App\Models;

class Topic extends BaseModel
{
    protected $fillable = [
        'name', 'slug', 'description', 'content', 'imagen',
        'position', 'visits_count', 'assessable',
        'topic_requirement_id', 'type_evaluation_id', 'duplicate_id'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Topic::class);
    }
}
