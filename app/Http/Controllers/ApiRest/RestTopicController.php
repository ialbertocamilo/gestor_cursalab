<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Course;
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
        $courses = $user->getCurrentCourses();

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

}
