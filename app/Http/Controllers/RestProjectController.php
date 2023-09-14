<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectUserRequest;

class RestProjectController extends Controller
{
    public function searchProjectUser(Project $project){
        $data = ProjectUser::searchProjectUser($project);
        return $this->success($data);
    }
    public function userSummary(){
        $summary = ProjectUser::userSummary();
        return $this->success($summary);
    }
    public function userProjects($type,Request $request){
        $tareas = ProjectUser::userProjects($type,$request);
        return $this->success($tareas);
    }
    public function storeUpdateUserProject(Project $project,ProjectUserRequest $request){
        $request->project = $project;
        $request->user = auth()->user();
        $status_label = ProjectUser::storeUpdateProjectUser($request);
        return $this->success(['msg'=>'La tarea se actualizó correctamente.','project_id'=>$project->id,'status_label'=> mb_strtolower($status_label)]);
    }
}
