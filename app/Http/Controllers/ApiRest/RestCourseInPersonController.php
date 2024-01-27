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

    public function listResources($course_id,$topic_id){
        if(!$course_id){
            return $this->error('Es necesario el curso_id.');
        }
        if(!$topic_id){
            return $this->error('Es necesario el topic_id.');
        }
        $resources = CourseInPerson::listResources($course_id,$topic_id);
        return $this->success(['resources'=>$resources]);
    }

    public function changeStatusEvaluation(Request $request){
        $data = $request->all();
        if(!isset($data['topic_id'])){
            return $this->error('Es necesario el topic_id.');
        }
        $result = CourseInPerson::changeStatusEvaluation($data);
        return $this->success(['result'=>$result]);
    }
}
