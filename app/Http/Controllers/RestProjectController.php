<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectUserRequest;
use App\Models\ProjectResources;

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
    public function downloadFile(Request $request){
        $project_resource = ProjectResources::find($request->multimedia_id);
        if(!$project_resource){
            return 'Archivo no encontrado';
        }
        try {
            return $project_resource->downloadFile();
        } catch (\Throwable $th) {
            return 'Archivo no encontrado';
        }
    }
    public function userProjects($type,Request $request){
        $tareas = ProjectUser::userProjects($type,$request);
        return $this->success($tareas);
    }
    public function storeUpdateUserProject(Project $project,ProjectUserRequest $request){
        $request->project = $project;
        $request->user = auth()->user();
        $status = ProjectUser::storeUpdateProjectUser($request);
        return $this->success([
            'msg'=>'La tarea se actualizó correctamente.',
            'project_id'=>$project->id,
            'status_label'=> $status?->name,
            'status_code'=> $status?->code,
        ]);
    }
}
