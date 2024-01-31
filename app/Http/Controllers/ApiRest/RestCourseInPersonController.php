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
        $data = CourseInPerson::listCoursesByUser($request);
        return $this->success($data);
    }

    public function getData(Request $request){
        $result = CourseInPerson::getData($request);
        return $this->success(['result'=>$result]);
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

    public function uploadSignature(Request $request,$topic_id){
        if(!isset($topic_id)){
            return $this->error('Es necesario el topic_id.');
        }
        $signature = $request->get('signature');
        if(!$signature){
            return $this->error('Es necesario la firma.');
        }
        $result = CourseInPerson::uploadSignature($signature,$topic_id);
        return $this->success(['result'=>$result]);
    }

    public function validateResource(Request $request,$topic_id){
        $type = $request->type;
        if(!isset($topic_id)){
            return $this->error('Es necesario el topic_id.');
        }
        if(!$type){
            return $this->error('Es necesario el tipo.');
        }
        $result = CourseInPerson::validateResource($type,$topic_id);
        return $this->success(['result'=>$result]);
    }

    public function startPoll($topic_id){
        if(!isset($topic_id)){
            return $this->error('Es necesario el topic_id.');
        }
        $result = CourseInPerson::startPoll($topic_id);
        return $this->success(['result'=>$result]);
    }
}
