<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Traits\CustomAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends BaseModel
{
    use HasFactory;
    protected $fillable = ['course_id','indications','active'];
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function resources()
    {
        return $this->hasMany(ProjectResources::class, 'project_id');
    }

    protected function getListSelectByType($request){
        $data = [];
        switch ($request->type) {
            case 'module':
                $current_workspace = get_current_workspace();
                $data =  Workspace::where('parent_id',$current_workspace->id)->where('active',1)->select('id','name')->get(); 
                break;
            case 'school':
                $data = School::where('active',1)
                ->whereHas('subworkspaces', function ($q) use ($request) {
                    $q->where('subworkspace_id', $request->subworkspace_id);
                })
                ->has('courses.project')->select('id','name')->get();
                break;
            case 'course':
                $current_workspace = get_current_workspace();
                $data = Course::has('project')->whereRelation('workspaces', 'id', $current_workspace->id)
                ->whereHas('schools', function ($q) use ($request) {
                    $q->where('id', $request->school_id);
                })->select('id','name')->get();
                break;
            case 'search-course':
                $current_workspace = get_current_workspace();
                $data = Course::leftJoin('projects AS p','p.course_id','=','courses.id')
                ->when(true, function($q) use ($request){
                    $request->q && $request->q != 'undefined'  ? $q->filtroName($request->q) : $q->doesntHave('project');
                })
                ->where('courses.active', 1)
                ->whereRelation('workspaces', 'id', $current_workspace->id)
                ->select(
                    'courses.id',
                    DB::raw('CASE WHEN p.id IS NULL THEN courses.name ELSE CONCAT(courses.name," (Ya tiene una tarea asignada)") END AS name'),
                    DB::raw('CASE WHEN p.id IS NULL THEN 0 ELSE 1 END AS disabled')
                )->whereNull('p.deleted_at')
                ->paginate(10)->items();
                
                break;
            case 'constraints':
                $data = config('project.constraints.admin');
                break;
        }
        return $data;
    }
    protected function search($request){
        $query = self::with([
            'course:id,name','course.schools:id,name',
            'course.schools.subworkspaces:id,name,logo'
        ])->select('id','course_id','indications','active');
        // ->withCount('usuario_cursos');

        // FILTERS
        if ($request->q){
            $query->wherehas('course',function($q) use ($request){
                $q->where('name', 'like', "%$request->q%");
            });
        }

        if ($request->subworkspace_id){
            $subworkspace_id = $request->subworkspace_id;
            $query->whereHas('course.schools.subworkspaces', function($q)use($subworkspace_id){
                $q->where('id', $subworkspace_id);
            });
        }

        if($request->school_id){
            $school_id = $request->school_id;
            $query->whereHas('course.schools', function($q)use($school_id){
                $q->where('id', $school_id);
            });
        }

        if($request->course_id){
            $course_id = $request->course_id;
            $query->whereHas('course', function($q)use($course_id){
                $q->where('id', $course_id);
            });
        }

        if ($request->active == 1){
            $query->where('active', ACTIVE);
        }

        if ($request->active == 2){
            $query->where('active', '<>', ACTIVE);
        }

        return $query->orderBy('created_at','desc')->paginate($request->paginate);
    }
    protected function storeUpdateRequest($request, $project = null){
        // try {
            $request_project = $request->project;
            DB::beginTransaction();
            if (!$project) {
                $project = new Project();
                $project->course_id = $request_project['course_id'];
            }
            $project->indications = isset($request_project['indications']) ? $request_project['indications'] : '';
            $project->save();

            ProjectResources::storeUpdateRequest($request,$project,'media_project_course',true);  
            DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return $e;
        // }
        return $project;
    }

    protected function editProject(Project $project){
        $project->load(['resources' => function ($q) {
            $q->where('from_resource','media_project_course')->select('id',DB::raw("'media' as type_resource "),'project_id','size','ext','path_file as file','filename as title');
        },'course:id,name']);
        return $project;
    }
}
