<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectStoreRequest;

class ProjectController extends Controller
{
    public function searchProject(Request $request){
        return $this->success([]);
    }
    public function store(ProjectStoreRequest $request){
        Project::storeRequest($request);
        return $this->success(['msg'=>'La tarea se creó correctamente.']);
    }
    public function update(ProjectStoreRequest $request){
        Tarea::updateRequest($request);
        return $this->success(['msg'=>'La tarea se actualizó correctamente.']);
    }

    public function changeStatus(Tarea $tarea){
        Tarea::changeStatus($tarea);
        return $this->success(['msg'=>'Se cambio el estado correctamente.']);
    }

    public function deleteTarea(Tarea $tarea){
        Tarea::deleteTarea($tarea);
        return $this->success(['msg'=>'Se elimino la tarea correctamente.']);
    }

    public function listConstraints(){
        $constraints = Tarea::listConstraints();
        return $this->success($constraints);
    }
    public function getListSelects(Request $request){
        $data = Project::getListSelectByType($request);
        return $this->success($data);
    }
}
