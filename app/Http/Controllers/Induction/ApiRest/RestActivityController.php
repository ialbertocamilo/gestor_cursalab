<?php

namespace App\Http\Controllers\Induction\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingAppResource;
use App\Mail\EmailTemplate;
use App\Models\Activity;
use App\Models\Ambiente;
use App\Models\Benefit;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\Course;
use App\Models\EntrenadorUsuario;
use App\Models\Internship;
use App\Models\InternshipUser;
use App\Models\MediaTema;
use App\Models\Meeting;
use App\Models\Poll;
use App\Models\Process;
use App\Models\ProcessSummaryActivity;
use App\Models\Project;
use App\Models\Question;
use App\Models\Speaker;
use App\Models\Stage;
use App\Models\SummaryTopic;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

    public function ActivityPasantia(Process $process, Activity $activity, Request $request)
    {
        $search = $request->get('search');

        $user = Auth::user();
        $internship = Internship::where('id', $activity->model_id)->first();
        $users = $internship && $internship->leaders ? json_decode($internship->leaders) : [];
        $internship_user_status_id = Taxonomy::getFirstData('internship', 'status', 'email_sent')?->id ?? null;

        $leaders = User::whereIn('id', $users)->select('id', 'name', 'lastname', 'surname', 'fullname');
        if($search)
        {
            $leaders->where(function($s) use ($search) {
                $s->where('name', 'like', '%'.$search.'%');
                $s->orWhere('lastname', 'like', '%'.$search.'%');
                $s->orWhere('surname', 'like', '%'.$search.'%');
            });
        }
        $leaders = $leaders->get();

        if($leaders) {
            foreach ($leaders as $leader) {
                $iu = InternshipUser::where('user_id', $user->id)
                                ->where('leader_id', $leader->id)
                                ->where('internship_id', $internship->id)
                                ->first();
                if($iu && $iu->status_id == $internship_user_status_id) {
                    $leader->status = 'sent';
                }
                else {
                    $leader->status = 'pending';
                }
            }
        }

        return $this->success(compact('leaders'));
    }

    public function SaveActivityPasantia(Process $process, Activity $activity,  Request $request )
    {
        $user = Auth::user();
        $internship = Internship::where('id', $activity->model_id)->first();
        $leader = User::where('id', $request->user_id)->select('id', 'name', 'lastname', 'surname', 'fullname', 'email', 'email_gestor')->first();
        $data = $request->data ?? [];

        $response['error'] = true;

        if($internship && $leader)
        {
            try {
                $internship_user = new InternshipUser();
                $internship_user->user_id = $user->id;
                $internship_user->internship_id = $internship->id;
                $internship_user->leader_id = $leader->id;

                if(isset($data['fecha1']))
                    $internship_user->meeting_date_1 = $data['fecha1'];
                if(isset($data['hora1']))
                    $internship_user->meeting_time_1 = $data['hora1'];
                if(isset($data['fecha2']))
                    $internship_user->meeting_date_2 = $data['fecha2'];
                if(isset($data['hora2']))
                    $internship_user->meeting_time_2 = $data['hora2'];

                $internship_user->status_id = Taxonomy::getFirstData('internship', 'status', 'email_sent')?->id ?? null;
                $internship_user->active = true;
                $internship_user->save();
                $this->sendEmail($user, $leader, $internship_user);

            } catch (\Exception $e) {
                $response['error'] = true;
            }
        }

        $response['error'] = false;

        return $this->success($response);
    }

    private function sendEmail($user, $lider, $internship)
    {
        //enviar codigo al email
        $mail_data = [ 'subject' => 'Solicitud de pasantía',
                        'meeting_date_1' => $internship->meeting_date_1,
                        'meeting_date_2' => $internship->meeting_date_2,
                        'meeting_time_1' => $internship->meeting_time_1,
                        'meeting_time_2' => $internship->meeting_time_2,
                        'lider_name' => $lider->name,
                        'user_name' => $user->fullname,
                        'user_email' => $user->email
                    ];

        $config = Ambiente::first();
        $mail_data['logo'] = get_media_url($config->logo);

        if(ENV('MULTIMARCA') == true){
            $mail_data['logo'] = 'https://statics-testing.sfo2.cdn.digitaloceanspaces.com/inretail-test2/images/wrkspc-40-wrkspc-35-logo-cursalab-2022-1-3-20230601193902-j6kjcrhock0inws-20230602170501-alIlkd31SSNTnIm.png';
        }
        if($lider->email) {
            // enviar email
            Mail::to($lider->email)
                ->send(new EmailTemplate('emails.pasantia_enviar_solicitud_a_lider', $mail_data));
        }
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
                                        ->whereNotNull('coach_id')
                                        ->first();

        $trainer_id = $checklistRpta?->coach_id ?? null;
        // if (!$checklistRpta) {
        //     $checklistRpta = ChecklistRpta::create([
        //         'checklist_id' => $checklist->id,
        //         'student_id' => $user->id,
        //         'percent' => 0
        //     ]);
        // }
        $response = CheckList::getStudentChecklistInfoById($checklist?->id, $user?->id, $trainer_id);

        $supervisor = User::where('id', $trainer_id)
                        ->with(['subworkspace'=>function($s){
                                    $s->select('id','name');
                                }])
                        ->select('id', 'name', 'lastname', 'surname', 'subworkspace_id')
                        ->first();
        $response['supervisor'] = $supervisor;
        $response['feedback_required'] = false;

        return response()->json($response, 200);
    }

    public function ActivityChecklistUserByTrainer(Process $process, Activity $activity, User $user, Request $request)
    {
        $trainer = Auth::user();
        $response = CheckList::getStudentChecklistInfoById($activity?->model_id, $user?->id, $trainer?->id);

        $summary_activity = ProcessSummaryActivity::where(['user_id'=> $user->id, 'activity_id' => $activity->id])->first();
        $status_finished = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;

        $response['editable'] = true;
        if($summary_activity?->status_id == $status_finished) {
            $response['editable'] = false;
        }

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

    public function RegisterUserChecklist(Process $process, User $user, Request $request )
    {
        $trainer = Auth::user();
        $activity = Activity::with('model')->where('id', $request->activity_id)->first();

        $response['message'] = 'No se pudo revisar la actividad';
        $response['error'] = true;

        if($activity->model_type == Checklist::class)
        {
            $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
            if($summary_activity)
            {
                $progress_checklist = CheckList::getStudentChecklistInfoById($activity->model_id, $user?->id, $trainer?->id);
                $activities = (isset($progress_checklist['actividades'])) ? $progress_checklist['actividades'] : [];
                $total = 0;
                foreach($activities as $act) {
                    if($act->estado == 'En proceso'){
                        $total += 30;
                    }
                    else if($act->estado == 'Cumple') {
                        $total += 100;
                    }
                }

                $summary_activity->status_id = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                $summary_activity->progress = count($activities) ? ($total / count($activities)) : 0;
                $summary_activity->save();
                $response['message'] = 'Se revisó la actividad <b>'.$activity->title.'</b> correctamente.';
                $response['error'] = false;
            }
        }

        return $this->success($response);
    }


    // public function updateProcessSummaryUser($user = null, $process_id = null, $stage_id = null)
    // {
    //     $tax_user_process_finished = Taxonomy::getFirstData('user-process', 'status', 'finished');
    //     $user_summary = $user->summary_process()->where('process_id', $process_id)->first();
    //     $user_summary_activities = $user->summary_process_stages()->where('stage_id', $stage_id)->first();

    //     $stages = Stage::where('process_id', $process_id)->get();
    //     dd($user_summary_activities);
    //     if($user_summary?->status_id != $tax_user_process_finished?->id) {
    //         dd($user_summary);
    //     }
    // }

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
                    $response['message'] = 'Completaste la actividad <b>'.$activity->title.'</b> correctamente.';
                }
            }
            else if($activity->model_type == Poll::class)
            {
                $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
                if($summary_activity)
                {
                    $summary_activity->status_id = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                    $summary_activity->progress = 100;
                    $summary_activity->save();
                    $response['message'] = 'Completaste la actividad <b>'.$activity->title.'</b> correctamente.';
                }
            }
            else if($activity->model_type == Meeting::class)
            {
                $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
                if($summary_activity)
                {
                    $summary_activity->status_id = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                    $summary_activity->progress = 100;
                    $summary_activity->save();
                    $response['message'] = 'Completaste la actividad <b>'.$activity->title.'</b> correctamente.';
                }
            }
            else if($activity->model_type == Topic::class)
            {
                $type_activity_temas = Taxonomy::getFirstData('processes', 'activity_type', 'temas')?->id;
                $type_activity_evaluacion = Taxonomy::getFirstData('processes', 'activity_type', 'evaluacion')?->id;

                if($type_activity_temas == $activity->type_id)
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
                        $response['message'] = 'Aún no terminas de revisar la actividad <b>'.$activity->title.'</b>.';
                    }
                    else if($progress >= 100) {
                        $status_progress = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                        $response['message'] = 'Completaste la actividad <b>'.$activity->title.'</b> correctamente.';
                    }
                    else {
                        $status_progress = Taxonomy::getFirstData('user-activity', 'status', 'in-progress')?->id;
                        $response['message'] = 'Aún no terminas de revisar la actividad <b>'.$activity->title.'</b>.';
                    }

                    $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
                    if($summary_activity)
                    {
                        $summary_activity->status_id = $status_progress;
                        $summary_activity->progress = $progress;
                        $summary_activity->save();
                    }
                }
                else if($type_activity_evaluacion == $activity->type_id)
                {
                    $summary_activity = ProcessSummaryActivity::firstOrCreate(['user_id'=> $user->id, 'activity_id' => $activity->id]);
                    if($summary_activity)
                    {
                        $summary_activity->status_id = Taxonomy::getFirstData('user-activity', 'status', 'finished')?->id;
                        $summary_activity->progress = 100;
                        $summary_activity->save();
                        $response['message'] = 'Completaste la actividad <b>'.$activity->title.'</b> correctamente.';
                    }
                }
            }
        }
        $response['error'] = false;

        return $this->success($response);
    }
}
