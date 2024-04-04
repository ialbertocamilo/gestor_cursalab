<?php

namespace App\Models\Master;

use App\Models\Master\TopicM;
use App\Models\Master\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseM extends Model
{
    use SoftDeletes;
    protected $connection = 'mysql_master';
    protected $table = 'courses';

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }
    public function modality()
    {
        return $this->belongsTo(Taxonomy::class, 'modality_id');
    }
    public function topics()
    {
        return $this->hasMany(TopicM::class, 'course_id');
    }
}
