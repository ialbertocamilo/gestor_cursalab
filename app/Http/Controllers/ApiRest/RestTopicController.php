<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Media;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Taxonomy;
use App\Models\MediaTema;
use App\Models\Requirement;
use App\Models\SummaryUser;
use App\Models\SummaryTopic;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SummaryCourse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RestTopicController extends Controller
{
    public function topics($course_id, Request $request)
    {
        $tiempoInicioApi = microtime(true);
        $user = Auth::user();
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user');
        $data = Topic::getDataToTopicsViewAppByUser($user, $courses, $request->school_id);
        $tiempo = microtime(true);
        $tiempoEjecucion = $tiempo - $tiempoInicioApi;
        info('Time execution:'.$tiempoEjecucion.'- Subworkspace_id: '.$user->subworkspace_id.' - '.$user->document);
        return $this->successApp(['data' => $data]);
    }
    public function topicsv2(Course $course, Request $request)
    {
        $user = Auth::user();
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user',byCoursesId:[$course?->id]);
        $data = Topic::getDataToTopicsViewAppByUser($user, $courses, $request->school_id);
        return $this->successApp(['data' => $data]);
    }

    public function listCoursesBySchool($school_id)
    {
        $user = Auth::user();
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user',bySchoolsId:[$school_id]);
        $data = Course::getDataToCoursesViewAppByUser($user, $courses);
        return $this->successApp(['data' => $data]);
    }
    public function listCoursesBySchoolV2($school_id)
    {
        $user = Auth::user();
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user',bySchoolsId:[$school_id]);
        $data = Course::getDataToCoursesViewAppByUserV2($user, $courses,$school_id);
        return $this->successAppV2($data);
    }


    public function reviewTopic(Topic $topic, $user = null)
    {
        if ($topic->course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $user = auth()->user() ?? $user;

        $topic->load('course');

        $summary_topic = SummaryTopic::select('id')
            ->where('topic_id', $topic->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$summary_topic) return $this->error("No se pudo revisar el tema.", 422);

        $reviewed_topic_taxonomy = Taxonomy::getFirstData('topic', 'user-status', 'revisado');
        $summary_topic->status_id = $reviewed_topic_taxonomy?->id;
        $summary_topic->last_time_evaluated_at = now();
        $summary_topic->save();

        SummaryCourse::updateUserData($topic->course, $user);
        SummaryUser::updateUserData($user);

        return $this->success(['msg' => "Tema revisado correctamente."]);
    }

    public function reviewTopicMedia( Request $request, Topic $topic, MediaTema $media, $user = null)
    {
        if ($topic->course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $user = auth()->user() ?? $user;

        $topic->load('course');

        $summary_topic = SummaryTopic::select('id','media_progress','last_media_access','last_media_duration','status_id')
            ->where('topic_id', $topic->id)
            ->where('user_id', $user->id)
            ->first();
        if (!$summary_topic) return $this->error("No se pudo revisar el contenido.", 422);
        $statuses = Taxonomy::select('id','name','code','group')
                    // ->where('group', 'topic')
                    ->where('type', 'user-status')->get();

        $medias = MediaTema::where('topic_id',$topic->id)->orderBy('position','ASC')->get();

        $media_progress = !is_null($summary_topic->media_progress) ? json_decode($summary_topic->media_progress) : null;

        if(!is_null($media_progress))
        {
            $user_progress_media = array();
            $media_in_progress = false;
            foreach($media_progress as $med_pro)
            {
                if(($med_pro->media_topic_id == $media->id))
                    $media_in_progress = true;

                $status_med = MediaTema::where('id',$med_pro->media_topic_id)->first();

                if(!is_null($status_med)) {
                    $status = ($med_pro->media_topic_id == $media->id) ? 'revisado' : $med_pro->status;
                    $status = ($status_med->embed) ? $status : 'revisado';
                    $status = ($status_med->type_id == 'link') ? 'revisado' : $status;
                    $last_media_duration = ($med_pro->media_topic_id == $media->id) ? $request->last_media_duration : $med_pro->last_media_duration;

                    array_push($user_progress_media, (object) array(
                        'media_topic_id' => $med_pro->media_topic_id,
                        'status' => $status,
                        'last_media_duration' => $last_media_duration
                    ));
                }
            }
            if(!$media_in_progress) {
                array_push($user_progress_media, (object) array(
                    'media_topic_id' => $media->id,
                    'status' => 'revisado',
                    'last_media_duration' => $request->last_media_duration
                ));
            }
            $summary_topic->media_progress = json_encode($user_progress_media);
        }
        else
        {
            $user_progress_media = array();
            foreach($medias as $med)
            {
                $status = ($med->id == $media->id) ? 'revisado' : 'por-iniciar';
                $status_med = MediaTema::where('id',$med->id)->first();
                $status = ($status_med->embed) ? $status : 'revisado';
                $status = ($status_med->type_id == 'link') ? 'revisado' : $status;
                $last_media_duration = ($med->id == $media->id) ? $request->last_media_duration : null;
                array_push($user_progress_media, (object) array('media_topic_id' => $med->id,
                'status'=> $status,
                'last_media_duration' => $last_media_duration));
            }
            $summary_topic->media_progress = json_encode($user_progress_media);
        }

        $summary_topic->last_media_access = $media->id;
        $summary_topic->last_media_duration = $request->last_media_duration;
        $summary_topic->save();

        $pending = false;
        $validate_summary_progress = json_decode($summary_topic->media_progress);
        if(is_array($validate_summary_progress) && count($validate_summary_progress) > 0){
            foreach($validate_summary_progress as $validate){
                if($validate->status != "revisado")
                    $pending = true;
            }
        }
        $summary_course = null;
        if(!$pending && !$topic->type_evaluation_id){
            $reviewed_topic_taxonomy = Taxonomy::getFirstData('topic', 'user-status', 'revisado');
            $summary_topic->status_id = $reviewed_topic_taxonomy?->id;
            $summary_topic->last_time_evaluated_at = now();
            $summary_topic->save();
            $summary_course = SummaryCourse::updateUserData($topic->course, $user);
            SummaryUser::updateUserData($user);
        }else{
            $summary_course = SummaryCourse::select('id','status_id','advanced_percentage')->where('user_id',$user->id)->where('course_id',$topic->course_id)->first();
        }
        $topic_status =  $statuses->where('id',$summary_topic->status_id)->first();
        $course_status = $statuses->where('id',$summary_course?->status_id)->first();
        $avaible_requirements_topic = false; //code aprobado o realizado
        $avaible_requirements_course = false;
        if(!$topic->type_evaluation_id){
            $avaible_requirements_topic =  $topic_status?->code == 'revisado';
        }
        $avaible_requirements_course = $course_status?->code == 'aprobado';
        $show_certification_to_user = $topic->course->show_certification_to_user && $course_status?->code == 'aprobado';

        return $this->success([
            'tema'=>[
                'id'=> $topic->id,
                'estado_tema' => $topic_status?->code,
                'estado_tema_str' => $topic_status?->name,
                'habilitar_requisitos' => $avaible_requirements_topic,
                'requirements' => ($avaible_requirements_topic)
                    ?   Requirement::where('model_type','App\\Models\\Topic')->where('requirement_id',$topic->id)->select('model_id')->pluck('model_id')
                    :   []
                // 'requirements' => $topic->requirements()->pluck('id')
            ],
            'course'=>[
                'id'=> $topic->course_id,
                'status' => $course_status?->code,
                'habilitar_requisitos' => $avaible_requirements_course,
                'requirements' => ($avaible_requirements_course)
                    ? Requirement::where('model_type','App\\Models\\Course')->where('requirement_id',$topic->course_id)->select('model_id')->pluck('model_id')
                    : [],
                'encuesta_habilitada' => $summary_course->advanced_percentage == 100 && $topic->course->polls->first(),
                'show_certification_to_user' => true,
                // 'requirements' => $topic->course->requirements()->pluck('id')
            ]
        ],'Contenido revisado correctamente.');
    }

    public function reviewTopicMediaDuration( Request $request, Topic $topic, MediaTema $media, $user = null)
    {
        if ($topic->course->hasBeenValidated())
            return ['error' => 0, 'data' => null];

        $user = auth()->user() ?? $user;

        $topic->load('course');

        $summary_topic = SummaryTopic::select('id','media_progress','last_media_access','last_media_duration')
            ->where('topic_id', $topic->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$summary_topic) return $this->error("No se pudo revisar el contenido.", 422);

        // $reviewed_topic_taxonomy = Taxonomy::getFirstData('topic', 'user-status', 'revisado');
        // $summary_topic->status_id = $reviewed_topic_taxonomy?->id;
        // $summary_topic->last_time_evaluated_at = now();
        // $summary_topic->save();

        // SummaryCourse::updateUserData($topic->course, $user);
        // SummaryUser::updateUserData($user);


        $medias = MediaTema::where('topic_id',$topic->id)->orderBy('position','ASC')->get();

        $media_progress = !is_null($summary_topic->media_progress) ? json_decode($summary_topic->media_progress) : null;

        if(!is_null($media_progress))
        {
            $user_progress_media = array();
            $media_in_progress = false;
            foreach($media_progress as $med_pro)
            {
                if(($med_pro->media_topic_id == $media->id))
                    $media_in_progress = true;

                $status_med = MediaTema::where('id',$med_pro->media_topic_id)->first();

                if(!is_null($status_med)) {
                    $status = $med_pro->status ?? 'iniciado';
                    $status = ($status_med->embed) ? $status : 'revisado';
                    $status = ($status_med->type_id == 'link') ? 'revisado' : $status;
                    $last_media_duration = ($med_pro->media_topic_id == $media->id) ? $request->last_media_duration : $med_pro->last_media_duration;

                    array_push($user_progress_media, (object) array(
                        'media_topic_id' => $med_pro->media_topic_id,
                        'status' => $status,
                        'last_media_duration' => $last_media_duration
                    ));
                }
            }
            if(!$media_in_progress) {
                array_push($user_progress_media, (object) array(
                    'media_topic_id' => $media->id,
                    'status' => 'revisado',
                    'last_media_duration' => $request->last_media_duration
                ));
            }
            $summary_topic->media_progress = json_encode($user_progress_media);
        }
        else
        {
            $user_progress_media = array();
            foreach($medias as $med)
            {
                $status = $med->status ?? 'iniciado';
                $status_med = MediaTema::where('id',$med->id)->first();
                $status = ($status_med->embed) ? $status : 'revisado';
                $status = ($status_med->type_id == 'link') ? 'revisado' : $status;
                $last_media_duration = ($med->id == $media->id) ? $request->last_media_duration : null;
                array_push($user_progress_media, (object) array('media_topic_id' => $med->id,
                'status'=> $status,
                'last_media_duration' => $last_media_duration));
            }
            $summary_topic->media_progress = json_encode($user_progress_media);
        }



        $summary_topic->last_media_access = $media->id;
        $summary_topic->last_media_duration = $request->last_media_duration;
        $summary_topic->save();

        return $this->success(['msg' => "Se actualizó la duración del contenido."]);
    }
    public function downloadMedia($topic_id,$media_id){
        return MediaTema::downloadMedia($media_id);
        $encryptionKey = 'donwload-offline'; // Reemplaza con tu clave secreta
        

        // Cifrar el contenido del archivo
        $encryptedData = openssl_encrypt($object['Body'], 'aes-256-cbc', $encryptionKey, 0, $object['Metadata']['iv']);

        // Retornar el archivo cifrado
        return response()->streamDownload(function () use ($encryptedData) {
            echo $encryptedData;
        }, $fileName . '.enc');
    }
}
