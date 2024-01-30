<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\Meeting;
use Illuminate\Http\Request;
use App\Models\CourseInPerson;
use App\Http\Controllers\Controller;

class RestCourseInPersonController extends Controller
{
    public function listCoursesByUser(Request $request){
        if(!$request->code){
            return $this->error('Es necesario el código.');
        }
        $user = auth()->user();
        $request->user = $user;
        $sessions_in_person = CourseInPerson::listCoursesByUser($request);
        $sessions_live = Meeting::getListMeetingsByUser($request);
        $sessions_course_live  = [];
        return $this->success(compact('sessions_in_person','sessions_live','sessions_course_live'));
    }

    public function listGuestsByCourse(Request $request,$course_id,$topic_id){
        $code = $request->code;
        if(!$code){
            return $this->error('Es necesario el código.');
        }
        $data = CourseInPerson::listGuestsByCourse($course_id,$topic_id,$code);
        return $this->success($data);
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

    public function getListMenu($topic_id){
        if(!isset($topic_id)){
            return $this->error('Es necesario el topic_id.');
        }
        $result = CourseInPerson::getListMenu($topic_id);
        return $this->success(['result'=>$result]);
    }

    public function takeAssistance(Request $request,$topic_id){
        if(!isset($topic_id)){
            return $this->error('Es necesario el topic_id.');
        }
        $data = $request->all();
        if(!isset($data['user_ids']) || count($data['user_ids']) == 0){
            return $this->error('Es necesario los documentos.');
        }
        if(!isset($data['action'])){
            return $this->error('Es necesario la acción.');
        }
        $result = CourseInPerson::takeAssistance($topic_id,$data);
        return $this->success(['result'=>$result]);
    }
}
