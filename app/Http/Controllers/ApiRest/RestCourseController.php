<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class RestCourseController extends Controller
{
    public function courses()
    {
        $user = Auth::user();
        $courses = $user->setCurrentCourses(return_courses: true);

        $data = Course::getDataToCoursesViewAppByUser($user, $courses);

        return $this->successApp(['data' => $data]);
    }
}
