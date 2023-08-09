<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Traits\CustomAudit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends BaseModel
{
    use HasFactory;
    protected $fillable = ['course_id','indications','active'];
    protected function getListSelectByType($request){
        $data = [];
        switch ($request->type) {
            // case 'module':
            //     $current_workspace = get_current_workspace();
            //     $data =  Workspace::where('parent_id',$current_workspace->id)->where('active',1)->select('id','name')->get(); 
            //     break;
            // case 'school':
            //     $data = School::where('active',1)
            //     ->whereHas('subworkspaces', function ($q) use ($request) {
            //         $q->where('subworkspace_id', $request->module_id);
            //     })
            //     ->has('courses')->select('id','name')->get();
            //     break;
            case 'course':
                $current_workspace = get_current_workspace();
                $data = Course::whereRelation('workspaces', 'id', $current_workspace->id)->filtroName($request->q)->where('active', 1)
                ->has('schools')->has('topics')->select('id','name')->get();
                break;
        }
        return $data;
    }
    protected function storeRequest($request){
        $request_project = $request->project;
        $project = new Project();
        $project->course_id = $request_project['course_id'];
        // $project->name = $request_project['name'];
        $project->indications = isset($request_project['indications']) ? $request_project['indications'] : '';
        $project->save();
        ProjectResources::storeUpdateRequest($request,$project,'media_project_course',true);      
    }
}
