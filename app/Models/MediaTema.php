<?php

namespace App\Models;

class MediaTema extends BaseModel
{
    protected $table = 'media_topics';

    protected $fillable = [
        'topic_id', 'title', 'value', 'embed', 'downloadable', 'position', 'type_id'
    ];

    protected $casts = [
        'embed' => 'boolean',
        'downloadable' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }
}
