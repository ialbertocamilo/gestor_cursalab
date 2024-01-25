<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Poll extends BaseModel
{
    protected $table = 'polls';

    protected $fillable = [
        'type_id', 'anonima', 'titulo', 'imagen', 'active', 'workspace_id', 'position'
    ];

    /*

        Relationships

    --------------------------------------------------------------------------*/


    public function questions()
    {
        return $this->hasMany(PollQuestion::class, 'poll_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function course()
    {
        return $this->belongsToMany(Course::class);
    }

    /*

        Methods

    --------------------------------------------------------------------------*/

    /**
     * Searches a record according a criteria
     *
     * @param $request
     * @return mixed
     */
    protected function search($request)
    {
        $session = $request->session()->all();
        $workspace = $session['workspace'];

        $query = self::withCount('questions')
            ->where('workspace_id', $workspace->id);

        if ($request->q)
            $query->where('titulo', 'like', "%$request->q%");


        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    /**
     * Calculates the count of related courses
     *
     * @return int
     */
    public function countCoursesRelated()
    {
        return DB::table('course_poll')
            ->where('poll_id', $this->id)
            ->count();
    }

    //  Functions to filters in report poll /resumen_encuesta
    protected function loadSchools($poll_id, $modules_id = []){
        $courses_id = DB::table('course_poll')->where('poll_id',$poll_id)->pluck('course_id');
        if(count($courses_id) == 0){
            return [];
        }
        $scholls =  School::select('id','name')
        ->whereRelationIn('subworkspaces', 'id', $modules_id)
        ->whereHas('courses',function($q)use ($courses_id){
            $q->whereIn('id',$courses_id);
        })->get();
        return $scholls;
    }
    protected function loadCourses($data){
        $courses_id = DB::table('course_poll')->where('poll_id',$data['poll_id'])->pluck('course_id');
        $courses =  Course::select('courses.id','courses.name','cs.school_id')
                    ->join('course_school as cs','cs.course_id','courses.id')
                    ->whereIn('id',$courses_id)
                    ->whereIn('cs.school_id',$data['schools'])->get();
                    // ->whereHas('schools',function($q) use($data){
                    //     $q->whereIn('school_id',$data['schools']);
                    // })->get();

        return $courses;
    }
    protected function loadPollReportData($filters){
        $pool_questions = PollQuestion::select('t.id','t.code','t.name','t.description','poll_questions.id as poll_question_id','poll_questions.titulo')
                            ->join('taxonomies as t','t.id','=','poll_questions.type_id')
                            ->where('poll_questions.poll_id',$filters['poll']['id'])
                            ->whereNull('poll_questions.deleted_at')->get();

        // $questions_type_califica = $this->resumePollQuestionTypeCalifica($pool_questions,$filters);
        $questions_type_califica = [];
        // if(get_current_workspace()->id != 25){
        //     $questions_type_califica = $this->resumePollQuestionTypeCalifica_v2($pool_questions,$filters);
        // }
        // $count_users = $user_to_response_poll->unique('user_id')->count();
        $count_users = 1;
        $resume = compact('count_users','questions_type_califica');
        $types_pool_questions = $pool_questions->unique('code');
        return compact('types_pool_questions','resume');
    }
    private function resumePollQuestionTypeCalifica_v2($pool_questions,$filters){
        // SELECT COUNT(id) as total from poll_question_answers where poll_question_id = 52 and JSON_EXTRACT(respuestas,'$[0].resp_cal') = 5
        // return $pool_questions->where('code','califica')->map(function($pool_question) use ($filters){
        $preguntas = [];
        $pool_questions_califica = $pool_questions->where('code','califica');
        foreach ($pool_questions_califica as $pool_question) {
            $base_query = DB::table('poll_question_answers as pqa')
            ->join('users as u','u.id','=','pqa.user_id')
            ->where('pqa.poll_question_id','=',$pool_question->poll_question_id)
            ->whereIn('pqa.course_id',$filters['courses_selected'])
            ->whereIn('u.subworkspace_id',$filters['modules'])
            ->where('u.active',1)
            ->whereNull('u.deleted_at')
            ->when(isset($filters['date']['end']), function ($q) use($filters){
                $q->whereBetween('pqa.created_at', [$filters['date']['start'],$filters['date']['end']]);
            });
            $prom_query = clone $base_query;
            $prom = $prom_query->select(DB::raw("avg(json_extract(respuestas, '$[0].resp_cal')) as count_resp_cal"))
                        ->whereIn(DB::raw("json_extract(respuestas, '$[0].resp_cal')"),[1,2,3,4,5])->first()->count_resp_cal;

            $total_query = clone $base_query;
            $total = $total_query->select(DB::raw('count(pqa.id) as count_resp_cal'))->first()->count_resp_cal;

            $user_califica_mb_query = clone $base_query;
            $user_califica_mb = $user_califica_mb_query->select(DB::raw('count(pqa.id) as count_resp_cal'))
                                            ->where(DB::raw("json_extract(respuestas, '$[0].resp_cal')"),'=',5)->first()->count_resp_cal;

            $user_califica_b_query = clone $base_query;
            $user_califica_b = $user_califica_b_query->select(DB::raw('count(pqa.id) as count_resp_cal'))
                                            ->where(DB::raw("json_extract(respuestas, '$[0].resp_cal')"),'=',4)->first()->count_resp_cal;

            $user_califica_r_query = clone $base_query;
            $user_califica_r = $user_califica_r_query->select(DB::raw('count(pqa.id) as count_resp_cal'))
                                            ->where(DB::raw("json_extract(respuestas, '$[0].resp_cal')"),'=',3)->first()->count_resp_cal;

            $user_califica_m_query = clone $base_query;
            $user_califica_m = $user_califica_m_query->select(DB::raw('count(pqa.id) as count_resp_cal'))
                                            ->where(DB::raw("json_extract(respuestas, '$[0].resp_cal')"),'=',2)->first()->count_resp_cal;

            $user_califica_mm_query = clone $base_query;
            $user_califica_mm = $user_califica_mm_query->select(DB::raw('count(pqa.id) as count_resp_cal'))
                                            ->where(DB::raw("json_extract(respuestas, '$[0].resp_cal')"),'=',1)->first()->count_resp_cal;


            $user_califica_tb2 = $user_califica_mb+$user_califica_b;

            $percent_califica_mb   = $user_califica_mb>0 ? round($user_califica_mb/$total*100,2) : 0;
            $percent_califica_b    = $user_califica_b>0 ? round($user_califica_b/$total*100,2) : 0;
            $percent_califica_r    = $user_califica_r>0 ? round($user_califica_r/$total*100,2) : 0;
            $percent_califica_m    = $user_califica_m>0 ? round($user_califica_m/$total*100,2) : 0;
            $percent_califica_mm   = $user_califica_mm>0 ? round($user_califica_mm/$total*100,2) : 0;
            $percent_user_califica_tb2   = $user_califica_tb2>0 ? round($user_califica_tb2/$total*100,2) : 0;

            $preguntas[] = [
                'titulo'=>$pool_question->titulo,
                'prom' => round($prom,2),
                'percent_califica_tb2'=>$percent_user_califica_tb2,
                'percent_califica_mb' => $percent_califica_mb,
                'percent_califica_b' => $percent_califica_b,
                'percent_califica_r' => $percent_califica_r,
                'percent_califica_m' => $percent_califica_m,
                'percent_califica_mm' => $percent_califica_mm
            ];
        };
        return $preguntas;
    }
    private function resumePollQuestionTypeCalifica($pool_questions,$filters){
        $user_to_response_poll = PollQuestionAnswer::
                select('poll_question_answers.id','poll_question_answers.user_id','poll_question_answers.poll_question_id','poll_question_answers.respuestas')
                ->whereIn('course_id',$filters['courses_selected'])
                ->whereIn('poll_question_id',$pool_questions->pluck('poll_question_id'))
                ->join('users as u','u.id','=','poll_question_answers.user_id')
                ->whereIn('u.subworkspace_id',$filters['modules'])
                ->where('u.active',1)
                ->whereNull('u.deleted_at')
                // ->wherehas('user',function($q)use($filters){
                //     $q->whereIn('subworkspace_id',$filters['modules'])->where('active',1);
                // })
                ->when($filters['date']['end'], function ($q) use($filters){
                    $q->whereBetween('poll_question_answers.created_at', [$filters['date']['start'],$filters['date']['end']]);
                })
                ->get();
        return $pool_questions->where('code','califica')->map(function($question_type_califica) use($user_to_response_poll){
            $questions_response = $user_to_response_poll->where('poll_question_id',$question_type_califica->poll_question_id)
                                                        ->map(function ($user_response) {
                                                            try {
                                                                $user_response->respuestas = json_decode($user_response->respuestas,true)[0]['resp_cal'];
                                                            } catch (\Throwable $th) {
                                                                //some response had this value-> "[]"
                                                                $user_response->respuestas ='-';
                                                            }
                                                            return $user_response;
                                                        })->where('respuestas','<>','-');

            $prom = $questions_response->avg('respuestas');

            $user_califica_mb = $questions_response->where('respuestas','5')->count();
            $user_califica_b = $questions_response->where('respuestas','4')->count();
            $user_califica_r = $questions_response->where('respuestas','3')->count();
            $user_califica_m = $questions_response->where('respuestas','2')->count();
            $user_califica_mm = $questions_response->where('respuestas','1')->count();

            $user_califica_tb2 = $user_califica_mb+$user_califica_b;


            $percent_califica_mb   = $user_califica_mb>0 ? round($user_califica_mb/count($questions_response)*100,2) : 0;
            $percent_califica_b    = $user_califica_b>0 ? round($user_califica_b/count($questions_response)*100,2) : 0;
            $percent_califica_r    = $user_califica_r>0 ? round($user_califica_r/count($questions_response)*100,2) : 0;
            $percent_califica_m    = $user_califica_m>0 ? round($user_califica_m/count($questions_response)*100,2) : 0;
            $percent_califica_mm   = $user_califica_mm>0 ? round($user_califica_mm/count($questions_response)*100,2) : 0;
            $percent_user_califica_tb2   = $user_califica_tb2>0 ? round($user_califica_tb2/count($questions_response)*100,2) : 0;

            return [
                'titulo'=>$question_type_califica->titulo,
                'prom' => round($prom,2),
                'percent_califica_tb2'=>$percent_user_califica_tb2,
                'percent_califica_mb' => $percent_califica_mb,
                'percent_califica_b' => $percent_califica_b,
                'percent_califica_r' => $percent_califica_r,
                'percent_califica_m' => $percent_califica_m,
                'percent_califica_mm' => $percent_califica_mm
            ];
        });
    }
     /**
     * Load active course polls
     *
     * @return void
     */
    protected function loadCoursePolls()
    {

        // $taxonomy = Taxonomy::getFirstData(
        //     'poll',
        //     'tipo',
        //     'xcurso'
        // );
        // if ($taxonomy) {
            $workspace = get_current_workspace();
            $polls = Poll::where('active', 1)
                // ->where('type_id', $taxonomy->id)
                ->with('type:id,code')
                ->where('workspace_id', $workspace->id)
                ->get();

            return $polls;
        // } else {
        //     return [];
        // }
    }
    protected function loadPollsByType($type){
        $workspace = get_current_workspace();
        $polls = Poll::where('active', 1)
            ->whereRelation('type','code',$type)
            ->with('type:id,code')
            ->where('workspace_id', $workspace->id)
            ->get();

        return $polls;
    }

    protected function updateSummariesAfterCompletingPoll($course, $user)
    {
        //        $summary_user = $user->summary;
        $summary_user = SummaryUser::getCurrentRow($user);

        $approved_status_taxonomy = Taxonomy::getFirstData('course', 'user-status', 'aprobado');

        $summary_course = SummaryCourse::getCurrentRow($course, $user);
        // if(!$summary_course){
        //     $summary_course = SummaryCourse::getCurrentRowOrCreate($course, $user);
        // }
        //        info("updateSummariesAfterCompletingPoll");
        //        info($summary_course->status_id);
        $summary_course->update(['status_id' => $approved_status_taxonomy?->id, 'advanced_percentage' => '100',]);
        //        info($summary_course->status_id);

        // $count_approved_courses = SummaryCourse::query()
        //     ->whereRelation('course', 'active', ACTIVE)
        //     ->whereRelation('status', 'code', 'aprobado')
        //     ->whereHas('course.type', fn ($q) => $q->where('code', '<>', 'free'))
        //     ->where('user_id', $user->id)
        //     ->count();
        $courses_id = $user->getCurrentCourses(withFreeCourses: false, withRelations: 'summary-user-update', only_ids: true);
        $count_approved_courses = SummaryCourse::where('user_id', $user->id)
                    ->whereRelation('status', 'code', 'aprobado')
                    ->whereRelation('course.type', 'code', '<>', 'free')
                    ->whereIn('course_id', $courses_id)
                    ->count();

        $general_percent = ($summary_user->courses_assigned > 0) ? (($count_approved_courses / $summary_user->courses_assigned) * 100) : 0;
        $general_percent = min($general_percent, 100);
        $general_percent = round($general_percent);

        $rank_user = User::calculate_rank($count_approved_courses, $summary_user->grade_average, $summary_user->attempts);


        $summary_user->update([
            'courses_completed' => $count_approved_courses,
            'advanced_percentage' => $general_percent,
            'score' => $rank_user
        ]);
    }
}
