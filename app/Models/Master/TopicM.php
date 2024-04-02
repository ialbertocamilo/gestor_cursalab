<?php

namespace App\Models\Master;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TopicM extends Model
{
    use SoftDeletes;
    protected $connection = 'mysql_master';
    protected $table = 'topics';

    public function evaluation_type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_evaluation_id');
    }

    public function medias()
    {
        return $this->hasMany(MediaTemaM::class, 'topic_id');
    }

    protected function getTopicsToMigrate($course,$filter_topics_by_date){
        $date_init = Carbon::today()->startOfDay()->format('Y-m-d H:i');
        return TopicM::with('medias:topic_id,title,value,embed,downloadable,position,type_id')
            ->where('course_id',$course->id)
            ->where('active',1)
            ->when(!$filter_topics_by_date, function ($q) use($date_init){
                $q->where('updated_at','>=',$date_init);
            })
            ->orderBy('position','ASC')
            ->get();
    }
}
