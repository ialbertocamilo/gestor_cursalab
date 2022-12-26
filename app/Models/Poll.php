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

    /**
     * Load active course polls
     *
     * @return void
     */
    protected function loadSchools($poll_id){
        $courses_id = DB::table('course_poll')->where('poll_id',$poll_id)->pluck('course_id');
        if(count($courses_id) == 0){
            return [];
        }
        $scholls =  School::select('id','name')->whereHas('courses',function($q)use ($courses_id){
            $q->whereIn('id',$courses_id);
        })->get();
        return $scholls;
    }
    protected function loadCourses($data){
        $courses_id = DB::table('course_poll')->where('poll_id',$data['poll_id'])->pluck('course_id');
        $courses =  Course::select('id','name')->whereIn('id',$courses_id)
        ->whereHas('schools',function($q) use($data){
            $q->whereIn('school_id',$data['schools']);
        })->get();

        return $courses;
    }
    protected function loadPollReportData($poll_id){
        $types_pool_questions = PollQuestion::select('t.id','t.code','t.name')
                            ->join('taxonomies as t','t.id','=','poll_questions.type_id')
                            ->where('poll_questions.poll_id',$poll_id)->groupBy('code')->get();
        return compact('types_pool_questions');
    }
    protected function loadCoursePolls()
    {

        $taxonomy = Taxonomy::getFirstData(
            'poll',
            'tipo',
            'xcurso'
        );
        if ($taxonomy) {
            $workspace = get_current_workspace();
            $polls = Poll::where('active', 1)
                ->where('type_id', $taxonomy->id)
                ->where('workspace_id', $workspace->id)
                ->get();

            return $polls;
        } else {
            return [];
        }
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
