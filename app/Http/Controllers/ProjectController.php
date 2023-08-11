<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Resources\ProjectSearchResourse;

class ProjectController extends Controller
{
    public function searchProject(Request $request){
        $projects = Project::search($request);
        ProjectSearchResourse::collection($projects);
        return $this->success($projects);
    }
    public function store(ProjectStoreRequest $request){
        Project::storeUpdateRequest($request);
        return $this->success(['msg'=>'El proyecto se creó correctamente.']);
    }
    public function update(ProjectStoreRequest $request,Project $project){
        Project::storeUpdateRequest($request,$project);
        return $this->success(['msg'=>'El proyecto se actualizó correctamente.']);
    }

    public function editProject(Project $project){
        $data = Project::editProject($project);
        return $this->success($data);
    }

    public function changeStatus(Project $project){
        $project->changeStatus();
        return $this->success(['msg'=>'Se cambio el estado correctamente.']);
    }

    public function deleteProject(Project $project){
        Project::destroyRequest($project);
        return $this->success(['msg'=>'Se elimino el proyecto correctamente.']);
    }
    
    public function listConstraints(){
        $constraints = Project::listConstraints();
        return $this->success($constraints);
    }

    public function getListSelects(Request $request){
        $data = Project::getListSelectByType($request);
        return $this->success($data);
    }
}
