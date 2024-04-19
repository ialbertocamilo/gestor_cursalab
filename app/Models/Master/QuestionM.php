<?php

namespace App\Models\Master;

use Carbon\Carbon;
use App\Models\Topic;
use App\Models\Question;
use App\Models\Master\TopicM;
use App\Models\Master\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionM extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mysql_master';
    protected $table = 'questions';

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
    public function topic()
    {
        return $this->belongsTo(TopicM::class);
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }
    protected function migrateQuestions(){
        $date_init = Carbon::today()->startOfDay()->format('Y-m-d H:i');
        $questionsGroupByTopics = QuestionM::with('type:id,code')
        // ->when(!$filter_by_date, function ($q) use($date_init){
        //     $q->where('updated_at','>=',$date_init);
        // })
        ->where('active',1)->get()->groupBy('topic_id');
        foreach ($questionsGroupByTopics as $topic_id => $questions_to_migrate) {
            $topics = Topic::with(['questions','evaluation_type:id,code','qualification_type'])
                    ->where('external_id',$topic_id)
                    ->whereHas('course',function($q){
                        $q->where('external_code','cursalab-university');
                    })
                    ->whereNotNull('type_evaluation_id')
                    ->select('id','course_id','type_evaluation_id','qualification_type_id')
                    ->get();
            foreach ($topics as $key => $topic) {
                    //code...
                $question_type_code = $topic->evaluation_type->code === 'qualified'
                ? 'select-options'
                : 'written-answer';
    
                $questions_duplicated = $topic->questions;
    
                $questions_id_to_create = $questions_to_migrate->pluck('id')->diff($questions_duplicated->pluck('external_id'));
                $questions_to_create = $questions_to_migrate->whereIn('id',$questions_id_to_create)->all();
                $questions_to_update = $questions_to_migrate->whereIn('id',$questions_duplicated->pluck('external_id'))->all();
                foreach ($questions_to_create as $question) {
                    Question::insertQuestionFromMaster($topic,$question_type_code,$question->toArray());
                }
                foreach ($questions_to_update as $question) {
                    Question::updateQuestionFromMaster($topic,$question_type_code,$question->toArray());
                }
            }
        }
    }
}
