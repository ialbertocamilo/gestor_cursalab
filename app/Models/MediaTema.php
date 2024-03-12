<?php

namespace App\Models;

class MediaTema extends BaseModel
{
    protected $table = 'media_topics';

    protected $fillable = [
        'topic_id', 'title', 'value', 'embed', 'downloadable', 'position', 'type_id','ia_convert'
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

    public function getSizeByCourse($course){
        return MediaTema::select('media.size')->join('media','media.file','media_topics.value')->whereHas('topic', function($q) use ($course) {
            $q->where('course_id', $course->id)->where('active',ACTIVE);
        })->groupBy('media.file')->get()->sum('size');
    }

    protected function dataSizeCourse($course,$has_offline=null,$size_limit_offline=null){
        $has_offline = $has_offline  ?? get_current_workspace()->functionalities()->where('code','course-offline')->first();
        if(!$has_offline || !$course->is_offline){
            return [
                'is_offline' => false,
                'limit'=>0 ,
                'has_space'=> true,
                'sum_size_topics' => 0
            ];
        }
        $sum_size_topics = MediaTema::getSizeByCourse($course);
        $limit_offline = Ambiente::getSizeLimitOffline(false,$size_limit_offline);
        return [
            'is_offline' => $course->is_offline,
            'limit'=> $limit_offline['size_limit_offline'].$limit_offline['size_unit'],
            'has_space'=> $sum_size_topics <= $limit_offline['size_in_kb'],
            'sum_size_topics' => formatSize(kilobytes:$sum_size_topics,parsed:false)
        ];
    }
}
