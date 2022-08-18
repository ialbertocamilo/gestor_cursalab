<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestTopicController extends Controller
{
    public function topics()
    {
        $user = Auth::user();
        $courses_id = $user->setCurrentCourses(return_courses_id: true);

        $data = Topic::getDataToTopicsViewAppByUser($user, $courses_id);


    }
}
