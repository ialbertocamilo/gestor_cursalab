<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Media;
use App\Models\MediaTema;
use App\Models\SummaryCourse;
use App\Models\SummaryTopic;
use App\Models\SummaryUser;
use App\Models\Taxonomy;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestTopicController extends Controller
{
    public function topics(Course $course, Request $request)
    {
        $user = Auth::user();
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user');

        $data = Topic::getDataToTopicsViewAppByUser($user, $courses, $request->school_id);

        return $this->successApp(['data' => $data]);
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

    public function reviewTopicMedia( Request $request, Topic $topic, Media $media, $user = null)
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
            foreach($media_progress as $med_pro)
            {
                $status = ($med_pro->media_topic_id == $media->id) ? 'revisado' : $med_pro->status;
                array_push($user_progress_media, (object) array('media_topic_id' => $med_pro->media_topic_id, 'status'=> $status));
            }
            $summary_topic->media_progress = json_encode($user_progress_media);
        }
        else
        {
            $user_progress_media = array();
            foreach($medias as $med)
            {
                $status = ($med->id == $media->id) ? 'revisado' : 'por-iniciar';
                array_push($user_progress_media, (object) array('media_topic_id' => $med->id, 'status'=> $status));
            }
            $summary_topic->media_progress = json_encode($user_progress_media);
        }



        $summary_topic->last_media_access = $media->id;
        $summary_topic->last_media_duration = $request->last_media_duration;
        $summary_topic->save();

        return $this->success(['msg' => "Contenido revisado correctamente."]);
    }

}
