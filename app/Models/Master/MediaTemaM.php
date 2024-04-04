<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaTemaM extends Model
{
    protected $connection = 'mysql_master';
    use SoftDeletes;
    protected $table = 'media_topics';

    protected $fillable = [
        'topic_id', 'title', 'value', 'embed', 'downloadable', 'position', 'type_id','ia_convert'
    ];
}
