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
    protected $fillable = ['workspace_id','course_id','indications','active', 'model_id', 'model_type'];
    use SoftDeletes;
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function resources()
    {
        return $this->hasMany(ProjectResources::class, 'project_id');
    }
    public function users(){
        return $this->hasMany(ProjectUser::class, 'project_id');
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
                // $data = Course::leftJoin('projects AS p','p.course_id','=','courses.id')
                $data = Course::
                when($request->q, function($q) use ($request){
                    // $request->q && $request->q != 'undefined'  ? $q->filtroName($request->q) : $q->doesntHave('project');
                    $q->filtroName($request->q) ;
                })
                ->where('courses.active', 1)
                ->whereRelation('workspaces', 'id', $current_workspace->id)
                ->select(
                    'courses.id',
                    'courses.name'
                    // 'p.id',
                    // 'p.deleted_at',
                    // DB::raw('CASE WHEN p.id IS NULL and p.deleted_at is null THEN courses.name ELSE CONCAT(courses.name," (Ya tiene una tarea asignada)") END AS name'),
                    // DB::raw('CASE WHEN p.id IS NULL and p.deleted_at is null THEN 0 courses 1 END AS disabled')
                )
                ->paginate(10)->map(function($course){
                    $course['disabled']  = 0;
                    if(Project::where('course_id',$course->id)->select('id')->first()){
                        $course['name'] = $course['name'].' (Ya tiene una tarea asignada)';
                        $course['disabled']  = 1;
                    }
                    return $course;
                });
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
        ])->select('id','course_id','indications','active')->withCount(['users' => function ($query) {
            $query->whereHas('status', function ($q) {
                $q->where('code', 'in_review');
            });
        }])->where('workspace_id',get_current_workspace()->id);
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
            if($request->hasFile('files')){
                $still_has_storage = Media::validateStorageByWorkspace($request->file('files'));
                if(!$still_has_storage){
                    return [
                        'msg'=>' Has superado la capacidad de almacenamiento dentro de la plataforma.',
                        'still_has_storage'=>false
                    ];
                }
            }
            $request_project = $request->project;
            DB::beginTransaction();
            if (!$project) {
                $project = new Project();
                $project->course_id = $request_project['course_id']; //crusbel - revisar cambio se puede cruzar con induccion con capa
                $project->model_id = $request_project['model_id'];
                $project->model_type = $request_project['model_type'];
                $project->workspace_id  = get_current_workspace()->id;
            }
            $project->indications = isset($request_project['indications']) ? $request_project['indications'] : '';
            $project->save();
            //Verificar espacio del storage:

            ProjectResources::storeUpdateRequest($request,$project,'media_project_course',true);
            DB::commit();
            return [
                'msg'=>'La tarea se ha creado correctamente',
                'still_has_storage'=>true,
                'project' => $project?->id ?? null
            ];
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
