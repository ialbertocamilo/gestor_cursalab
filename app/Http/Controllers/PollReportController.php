<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\Request;

class PollReportController extends Controller
{
    public function loadInititalData(){
        $polls = Poll::loadCoursePolls();
        $current_workspace = get_current_workspace();
        $modules_workspace = $current_workspace->subworkspaces;
        $modules = [];
        foreach ($modules_workspace as $module) {
            $modules[] = [
                'id'=>$module->id,
                'name'=>$module->name
            ];
        }
        return $this->success(compact('polls','modules'));
    }

    public function loadSchools($poll_id){
        $modules = request('modules');
        $schools = Poll::loadSchools($poll_id, $modules);
        return  $this->success(compact('schools'));
    }

    public function loadCourses(Request $request){
        $courses = Poll::loadCourses($request->all());
        return  $this->success(compact('courses'));
    }

    public function loadPollReportData(Request $request){
        $data = Poll::loadPollReportData($request->all());
        return  $this->success(compact('data'));
    }
}
