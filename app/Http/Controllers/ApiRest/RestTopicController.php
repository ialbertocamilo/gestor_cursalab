<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Course;
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
}
