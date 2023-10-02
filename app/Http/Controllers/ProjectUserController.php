<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectUser;
use Illuminate\Http\Request;
use App\Models\ProjectResources;
use App\Http\Requests\ProjectUserUpdateRequest;
use App\Http\Resources\ProjectUserSearchResource;

class ProjectUserController extends Controller
{
    public function search(Request $request,Project $project){
        $data = ProjectUser::search($request,$project);
        ProjectUserSearchResource::collection($data);
        return $this->success($data);
    }
    public function downloadZipFiles($project_user_id,Request $request){
        $request->project_user_id = $project_user_id;
        $name_zip = ProjectUser::downloadZipFiles($request);
        return response()->download(public_path($name_zip), $name_zip)->deleteFileAfterSend(true);
    }
    public function downloadMassiveZipFiles(Request $request){
        $project_users_id = $request->puid;
        $project_id = $request->project_id;
        $name_zip = ProjectUser::downloadMassiveZipFiles($project_users_id,$project_id);
        return response()->download(public_path($name_zip), $name_zip)->deleteFileAfterSend(true);
    }
    public function downloadFile(ProjectResources $project_resource){
        return $project_resource->downloadFile();
    }
    public function update(ProjectUser $project_user,ProjectUserUpdateRequest $request){
        $request->project_user = $project_user;
        ProjectUser::updateProjectUser($request);
        return $this->success([]);
    }

    public function listStatus($type){
        $codes = ($type=='select') ? ['observed','disapproved','passed'] :  ['pending','in_review','observed','disapproved','passed'];
        $state = ProjectUser::getStatusCodes($codes);
        return $this->success($state);
    }
}
