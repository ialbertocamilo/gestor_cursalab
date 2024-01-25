<?php

namespace App\Http\Controllers\ApiRest;

use Illuminate\Http\Request;
use App\Models\CourseInPerson;
use App\Http\Controllers\Controller;

class RestCourseInPersonController extends Controller
{
    public function listCoursesByUser(Request $request){
        $code = $request->code;
        if(!$code){
            return $this->error('Es necesario el código.');
        }
        $user = auth()->user();
        $sessions = CourseInPerson::listCoursesByUser($user,$code);
        return $this->success(['sessions'=>$sessions]);
    }

    public function listGuestsByCourse(Request $request,$course_id){
        $code = $request->code;
        if(!$code){
            return $this->error('Es necesario el código.');
        }
        $users = CourseInPerson::listGuestsByCourse($course_id);
        return $this->success(['users'=>$users]);
    }
}
