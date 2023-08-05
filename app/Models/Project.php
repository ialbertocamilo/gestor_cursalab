<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public static function getListSelectByType($request){
        $data = [];
        switch ($request->type) {
            case 'module':
                $current_workspace = get_current_workspace();
                $data =  Workspace::where('parent_id',$current_workspace->id)->where('active',1)->select('id','name')->get(); 
                break;
            case 'school':
                $data = School::where('active',1)
                ->whereHas('subworkspaces', function ($q) use ($request) {
                    $q->where('subworkspace_id', $request->module_id);
                })
                ->has('courses')->select('id','name')->get();
                break;
            case 'course':
                $data = Course::where('active',1)->whereHas('schools', function ($q) use ($request) {
                    $q->where('school_id', $request->school_id);
                })->has('topics')->select('id','name')->get();
                break;
        }
        return $data;
    }
}
