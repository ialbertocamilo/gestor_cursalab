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
                $data = Course::doesntHave('project')->whereRelation('workspaces', 'id', $current_workspace->id)->filtroName($request->q)->where('active', 1)
                ->has('schools')->has('topics')->select('id','name')->get();
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

        if ($request->q){
            $query->wherehas('course',function($q) use ($request){
                $q->where('name', 'like', "%$request->q%");
            });
        }

        // if ($request->modulo_id){
        //     $modulos = $request->modulo_id;
        //     $query->whereHas('curso', function($q)use($modulos){
        //         $q->whereIn('config_id', $modulos);
        //     });
        // }
        // if($request->escuela_id){
        //     $escuelas = $request->escuela_id;
        //     $query->whereHas('curso', function($q)use($escuelas){
        //         $q->whereIn('categoria_id', $escuelas);
        //     });
        // }
        // if($request->curso_id){
        //     $cursos = $request->curso_id;
        //     $query->whereHas('curso', function($q)use($cursos){
        //         $q->whereIn('id', $cursos);
        //     });
        // }
        // if (isset($request->active)){
        //     $query->where('active',$request->active);
        // }
        return $query->orderBy('created_at','desc')->paginate($request->paginate);
    }
    protected function storeRequest($request, $project = null){
        try {
            $request_project = $request->project;
            $project = new Project();
            DB::beginTransaction();
            if (!$project) {
                $project = new Project();
            }
            $project->course_id = $request_project['course_id'];
            $project->indications = isset($request_project['indications']) ? $request_project['indications'] : '';
            $project->save();

            ProjectResources::storeUpdateRequest($request,$project,'media_project_course',true);  
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
        return $project;
    }
}
