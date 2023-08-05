<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function searchProject(Request $request){
        return $this->success([]);
    }

    public function getListSelects(Request $request){
        $data = Project::getListSelectByType($request);
        return $this->success($data);
    }
}
