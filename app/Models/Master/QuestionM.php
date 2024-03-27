<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionM extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mysql_master';
    protected $fillable = [
        'topic_id', 'type_id', 'pregunta',
        'rptas_json', 'rpta_ok', 'active', 'required', 'score',
    ];

    protected $casts = [
        'rptas_json' => 'array',
        // 'score' => 'integer',
    ];

    public $defaultRelationships = [
        'type_id' => 'type',
        'topic_id' => 'topic'
    ];

    protected function getQuestionsToMigrate(){
        
    }
}
