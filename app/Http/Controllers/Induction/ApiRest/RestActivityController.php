<?php

namespace App\Http\Controllers\Induction\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingAppResource;
use App\Models\Activity;
use App\Models\Benefit;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\Course;
use App\Models\EntrenadorUsuario;
use App\Models\MediaTema;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\Process;
use App\Models\ProcessSummaryActivity;
use App\Models\Project;
use App\Models\Question;
use App\Models\Speaker;
use App\Models\SummaryTopic;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class RestActivityController extends Controller
{
    public function ActivityMeeting(Process $process, Meeting $meeting, Request $request)
    {
        $meetings = Meeting::with('type', 'status', 'account.service', 'workspace', 'host', 'attendants.type')
                            ->withCount('attendants')
                            ->where('id', $meeting->id)

                            ->whereHas('attendants', function ($q) use ($request) {
                                $q->where('usuario_id', auth()->user()->id);
                            })
                            ->first();

        $res = new MeetingAppResource($meetings);

        $result['data'] = json_decode($res->toJson(), true);

        return $this->successApp($result);
    }

    public function ActivityPoll(Process $process, Poll $poll, Request $request)
    {
        $questions = $poll->questions()->with('type:id,code')
                     ->where('active', ACTIVE)
                     ->select('id', 'poll_id', 'titulo', 'type_id', 'opciones')
                     ->get();

        return $this->success(compact('questions'));
    }

    public function ActivityTopic(Process $process, Topic $topic, Request $request)
    {

        $topics_data = null;

        if($topic)
        {
            $media_topics = MediaTema::where('topic_id',$topic->id)->orderBy('position')->get();
            $summary_topic =  $topic->summaries->first();

            $media_progress = !is_null($summary_topic?->media_progress) ? json_decode($summary_topic?->media_progress) : null;
            foreach ($media_topics as $media) {
                unset($media->created_at, $media->updated_at, $media->deleted_at);
                $media->full_path = !in_array($media->type_id, ['youtube', 'vimeo', 'scorm', 'link'])
                    ? route('media.download.media_topic', [$media->id]) : null;

                $media->status_progress = 'por-iniciar';
                $media->last_media_duration = null;

                if(!is_null($media_progress)){
                    foreach($media_progress as $medp){
                        if($medp->media_topic_id == $media->id){
                            $media->status_progress = $medp->status;
                            $media->last_media_duration = $medp->last_media_duration;
                            break;
                        }
                    }
                }
            }

            $media_embed = array();
            $media_not_embed = array();
            foreach ($media_topics as $med) {
                if($med->embed)
                    array_push($media_embed, $med);
                else
                    array_push($media_not_embed, $med);
            }


            $last_media_access = $summary_topic?->last_media_access;
            $last_media_duration = $summary_topic?->last_media_duration;

            $media_topic_progress = array('media_progress' => $media_progress,
                                        'last_media_access' => $last_media_access,
                                        'last_media_duration' => $last_media_duration);

            $topics_data['resources'] = [
                'id' => $topic->id,
                'nombre' => $topic->name,
                'imagen' => $topic->imagen,
                'contenido' => $topic->content,
                'media' => $media_embed,
                'media_not_embed' => $media_not_embed,
                'media_topic_progress'=>$media_topic_progress,
                'evaluable' => $topic->assessable ? 'si' : 'no',
                'tipo_ev' => $topic->evaluation_type->code ?? NULL,
                'nota_sistema_nombre' => $topic_status['system_grade_name'] ?? NULL,
                'nota_sistema_valor' => $topic_status['system_grade_value'] ?? NULL,
            ];
        }
        return $this->successApp(['data' => $topics_data]);
    }


    public function ActivityChecklist(Process $process, Checklist $checklist, Request $request)
    {
        $user = Auth::user();

        $checklistRpta = ChecklistRpta::where('checklist_id',$checklist->id)
                                        ->where('student_id', $user->id)
                                        ->first();
        if (!$checklistRpta) {
            $checklistRpta = ChecklistRpta::create([
                'checklist_id' => $checklist->id,
                'student_id' => $user->id,
                'percent' => 0
            ]);
        }
        $response = CheckList::getStudentChecklistInfoById($checklist?->id);

        return response()->json($response, 200);
    }
    public function ActivityChecklistUserByTrainer(Process $process, Checklist $checklist, User $user, Request $request)
    {
        $trainer = Auth::user();
        $response = CheckList::getStudentChecklistInfoById($checklist?->id, $user?->id, $trainer?->id);

        return response()->json($response, 200);
    }


    public function ActivityAssessment(Process $process, Topic $topic)
    {
        $user = Auth::user();

        // $topic->load('evaluation_type','course');

        // if ($topic->course->hasBeenValidated())
        //     return ['error' => 0, 'data' => null];

        // $is_qualified = $topic->evaluation_type->code == 'qualified';
        // $is_random = $is_qualified;
        // $type_code = $is_qualified ? 'select-options' : 'written-answer';

        // if (!$topic) return response()->json(['data' => ['msg' => 'Not found'], 'error' => true], 200);
        // if ($is_qualified AND !$topic->evaluation_verified) return response()->json(['data' => ['msg' => 'Not verified'], 'error' => true], 200);

        // $summary = SummaryTopic::where('topic_id', $topic->id)->where('user_id', $user->id)->first();

        // if (!$summary)
        //     $summary = SummaryTopic::storeData($topic, $user);

        // $row = SummaryTopic::setStartQuizData($topic);

        // if ($row->hasNoAttemptsLeft(null,$topic->course))
        //     return response()->json(['error' => true, 'msg' => 'Sin intentos.'], 200);

        // if ($row->isOutOfTimeForQuiz())
        //     return response()->json(['data' => ['msg' => 'Fuera de tiempo'], 'error' => true], 200);

        // $limit = NULL;

        // if ($type_code == 'written-answer') {

        //     $questions = Question::getQuestionsForQuiz($topic, $limit, $is_random, $type_code);

        // } else {

        //     $questions = Question::getQuestionsWithScoreForQuiz($topic, $limit, $is_random, $type_code);
        // }

        // if (count($questions) == 0)
        //     return response()->json(['error' => true, 'data' => null], 200);

        // $data = [
        //     'nombre' => $topic->name,
        //     'posteo_id' => $topic->id,
        //     'curso_id' => $topic->course_id,
        //     'preguntas' => $questions,
        //     'tipo_evaluacion' => $topic->evaluation_type->code ?? NULL,
        //     'attempt' => [
        //         'started_at' => $row->current_quiz_started_at->format('Y/m/d H:i'),
        //         'finishes_at' => $row->current_quiz_finishes_at->format('Y/m/d H:i'),
        //         'diff_in_minutes' => now()->diffInMinutes($row->current_quiz_finishes_at),
        //     ],
        // ];

        // return response()->json(['error' => false, 'data' => $data], 200);
        // *********************

        $topics_data = null;

        if($topic)
        {
            $summary_topic =  $topic->summaries->first();

            $course = $topic->course;

            $max_attempts = $course->mod_evaluaciones['nro_intentos'];

            $user_status = Taxonomy::where('type', 'user-status')->get();
            $statuses_topic = $user_status->where('group', 'topic')->where('type', 'user-status')
            ->whereIn('code', ['aprobado', 'realizado', 'revisado'])
                ->pluck('id')->toArray();

            $topic_status = $topic->getTopicStatusByUser($topic, $user, $max_attempts, $statuses_topic);

            $topics_data = [
                'id' => $topic->id,
                'nombre' => $topic->name,
                // 'requisito_id' => $topic_status['topic_requirement'],
                // 'requirements' => $topic_status['requirements'],
                // 'imagen' => $topic->imagen,
                'contenido' => $topic->content,
                'evaluable' => $topic->assessable ? 'si' : 'no',
                'tipo_ev' => $topic->evaluation_type->code ?? NULL,
                // 'nota' => $topic_status['grade'],
                // 'nota_sistema_nombre' => $topic_status['system_grade_name'] ?? NULL,
                // 'nota_sistema_valor' => $topic_status['system_grade_value'] ?? NULL,
                'disponible' => $topic_status['available'],
                'intentos_restantes' => $topic_status['remaining_attempts'],
                // 'estado_tema' => $topic_status['status'],
                // 'estado_tema_str' => $topic_status_arr[$topic_status['status']],
                'mod_evaluaciones' => $course->getModEvaluacionesConverted($topic),
            ];
        }
        return response()->json(['error' => false, 'data' => $topics_data], 200);
        // *********
    }

    public function RegisterActivity(Process $process,  Request $request )
    {
        $user = Auth::user();
        $activity = Activity::with('model')->where('id', $request->activity_id)->first();
        if($activity)
        {
            if($activity->model_type == Project::class)
            {
                $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
                if($summary_activity)
                {
                    $summary_activity->status_id = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                    $summary_activity->progress = 100;
                    $summary_activity->save();
                }
            }
            else if($activity->model_type == Topic::class)
            {
                $activity_topic = $activity->model;

                $media_topics = MediaTema::where('topic_id',$activity_topic->id)->orderBy('position')->get();

                $summary_topic = SummaryTopic::select('id','media_progress','last_media_access','last_media_duration')
                                                ->where('topic_id', $activity_topic->id)
                                                ->where('user_id', $user->id)
                                                ->first();

                $media_progress = !is_null($summary_topic?->media_progress) ? json_decode($summary_topic?->media_progress) : null;

                $count_media_topics = $media_topics->count();
                $count_media_completed = 0;

                foreach ($media_topics as $media) {
                    if(!is_null($media_progress)){
                        foreach($media_progress as $medp){
                            if($medp->media_topic_id == $media->id){
                                if($medp->status == 'revisado')
                                    $count_media_completed++;
                                break;
                            }
                        }
                    }
                }

                $progress = $count_media_completed > 0 && $count_media_topics > 0 ? round(((($count_media_completed * 100 / $count_media_topics) * 100) / 100),2) : 0;

                if($progress == 0) {
                    $status_progress = Taxonomy::getFirstData('user-activity', 'status', 'pending')?->id;
                }
                else if($progress >= 100) {
                    $status_progress = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                }
                else {
                    $status_progress = Taxonomy::getFirstData('user-activity', 'status', 'in-progress')?->id;
                }

                $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
                if($summary_activity)
                {
                    $summary_activity->status_id = $status_progress;
                    $summary_activity->progress = $progress;
                    $summary_activity->save();
                }

            }
        }

        return response()->json(['error' => false], 200);
    }
}
