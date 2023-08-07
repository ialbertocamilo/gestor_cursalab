<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use App\Http\Resources\EvaluationDetailReportResource;
use App\Http\Resources\EvaluationsReportResource;
use App\Models\Course;
use App\Models\School;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Workspace;
use Illuminate\Http\Request;

class EvaluationsReportController extends Controller
{
    public function loadModules(Request $request) 
    {
        $workspace = get_current_workspace();
        $subworkspaces = $workspace->subworkspaces()->select('id','codigo_matricula','name')->get();

        return $this->success($subworkspaces);
    }

    public function loadSchools(Request $request) 
    {
        $schools = School::select('id','name','code')
            ->join('school_subworkspace as ss','ss.school_id','schools.id')
            ->whereIn('ss.subworkspace_id', explode(',', $request->modules) )
            ->get();

        return $this->success($schools);
    }

    public function loadCourses(Request $request) 
    {
        $courses = Course::select('id','name','code')
                         ->join('course_school as cs', 'cs.course_id', 'courses.id')
                         ->whereIn('cs.school_id', explode(',', $request->schools ))
                         ->get();

         return $this->success($courses);
    }
    
    public function loadTopics(Request $request) 
    {
        $topics = Topic::select('id', 'name')
                       ->whereIn('course_id', explode(',', $request->courses))
                       ->get();

        return $this->success($topics); 
    }

    public function loadEvaluationReportData(Request $request) 
    {
        $workspace = get_current_workspace();
        $request->subworkspaces = $workspace->subworkspaces;

        $evaluation_type = Taxonomy::where('group', 'topic')
                                    ->where('type', 'evaluation-type')
                                    ->where('code', 'qualified')
                                    ->first();

        $topic_query = Topic::select('id', 'name', 'course_id', 'assessable')
                            ->with([
                                'course:id,name,code'=> [
                                    'schools:id,name' => ['subworkspaces:id'] 
                                ]
                            ])
                            ->whereIn('course_id', $request->courses)
                            ->where('assessable', 1)
                            ->where('type_evaluation_id', $evaluation_type->id);

        if($request->topics) {
            $topic_query->whereIn('id', $request->topics);
        }

        $topic_query = $topic_query->withCount('summaries as total_evaluations')
                        ->withSum('summaries as total_corrects', 'correct_answers')
                        ->withSum('summaries as total_incorrects', 'failed_answers')
                        ->get();

        $topics = EvaluationsReportResource::collection($topic_query);

        return $this->success($topics);
    }

    public function loadEvaluationDetailReportData(Topic $topic, Request $request) 
    {
        $topic_questions = $topic->questions()->select('id', 'pregunta','rpta_ok')->get();
        $topic_summaries = $topic->summaries()->select('id', 'answers','status_id','topic_id')
                                 ->whereNotNull('answers')->get();

        $questions_data = Arr::keyBy($topic_questions->toArray(), 'id');
        $topic_question_data = [];

        $topic_summaries->each(function($answer) use(&$topic_question_data, $questions_data) {

            foreach ($answer->answers as ['preg_id' => $preg_id, 'opc' => $opc]) {
                
                ['rpta_ok' => $rpta_ok, 'pregunta' => $pregunta] = $questions_data[$preg_id];
                $is_correct = ($opc == $rpta_ok);
                
                // === total evluaciones ===
                if(isset($topic_question_data[$preg_id])) {
                    $topic_question_data[$preg_id]['total_evaluations']++;

                    // === correctas - incorrectas ===
                    if ($is_correct) {
                        $topic_question_data[$preg_id]['total_corrects']++;
                    }else{
                        $topic_question_data[$preg_id]['total_incorrects']++;
                    }
                    // === correctas - incorrectas ===

                } else {
                    $topic_question_data[$preg_id] = [ 'total_evaluations' => 1,
                                                       'total_corrects' => ($is_correct) ? 1 : 0,
                                                       'total_incorrects' => ($is_correct) ? 1 : 0 ];
                }
                // === total evluaciones ===
            }
        });

        // === parseo questions ===
        foreach ($questions_data as $key => $question) {

            if(isset($topic_question_data[$key])) {
                $questions_data[$key]['results'] = $topic_question_data[$key];
            }else{
                $questions_data[$key]['results'] = [ 'total_evaluations' => 0,
                                                     'total_corrects' => 0,
                                                     'total_incorrects' => 0 ];
            }
        }
        // === parseo questions ===

        $questions_data = EvaluationDetailReportResource::collection($questions_data);

        return $this->success($questions_data);
    }

    public function generateEvaluationReport(Request $request) 
    {

    }
}
