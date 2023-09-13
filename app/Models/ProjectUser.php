<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectUser extends Model
{
    use HasFactory;
    protected $table = 'project_users';
    protected $fillable = ['project_id','user_id','status_id','msg_to_user'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function tarea()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }
    protected function userSummary(){
        $data['count_approved'] = 0;
        $data['count_pending'] = 0;
        $user = auth()->user();
        $projects = $this->projectsByUser($user);
        if(count($projects)>0){
            $status_ids = self::getStatusCodes(['disapproved','passed']);
            $data['count_approved'] = self::whereIn('project_id',$projects->pluck('id'))
                                    ->where('user_id',$user->id)
                                    ->whereIn('status_id',$status_ids->pluck('id'))
                                    ->count();
            $data['count_pending'] = $projects->count() - $data['count_approved'];
        }
        $data['recommendations'] = config('project.recommendations');
        return $data;
    }
    protected function userProjects($type){
        $user = auth()->user();
        $projects = $this->projectsByUser($user,true);
        $response=[];
        if(count($projects)>0){
            $status_code = ($type=='pendiente') ? ['pending','in_review','observed'] : ['disapproved','passed'];
            $status =  self::getStatusCodes($status_code);

            $usuario_tarea = self::whereIn('project_id',$projects->pluck('id'))
                            ->with(['status'=>function($q){
                                $q->select('id','name','code');
                            }])
                            ->where('user_id',$user->id)
                            ->get();
            foreach ($projects as $project) {
                $find_project_user = $usuario_tarea->where('project_id',$project->id)->first();
                $project->code_status = 'pending';
                $project->status = 'Pendiente';
                if(isset($find_project_user->status)){
                    $project->code_status = $find_project_user->status->code;
                    $project->status = $find_project_user->status->name;
                }
                
                in_array($project->code_status,$status_code) && ($response[]=$project);
            }
        }
       
        return $response;
    }

    protected function search($request , $project){
        $course = $project->load(['course:id,name','course.segments']);
        $users_id =  array_unique($project->course->usersSegmented($project?->course?->segments, $type = 'users_id'));

        $query = User::whereIn('id',$users_id)->with(['subworkspace:id,name','projects'=>function($q) use($project){
            $q->select('id','user_id','status_id','msg_to_user')->where('project_id',$project->id);
        },'projects.status'=>function($q){
            $q->select('id','name');
        },'project_resources'=>function($q)use($project){
            $q->where('project_id',$project->id)->select('id','project_id','path_file','filename','type_media','from_resource','type_id');
        }])
        ->where('active',1)
        ->select('id','name', 'lastname', 'surname', 'document','subworkspace_id');
        if ($request->q){
            $query->where(function($q) use ($request){
                $q->where('name', 'like', "%$request->q%")
                ->orWhere('document', 'like', "%$request->q%")
                ->orWhere('lastname', 'like', "%$request->q%")
                ->orWhere('surname', 'like', "%$request->q%")
                ->orWhere(DB::raw("CONCAT(name,' ',lastname,' ',surname)"), 'LIKE', '%' . $request->q . '%');
            });
        }
        if($request->subworkspace_id){
            $query->where('subworkspace_id',$request->subworkspace_id);
        }
        // if($request->q_group_dnis){
        //     $q_group_dnis = explode(' ',$request->q_group_dnis);
        //     $query->whereIn('dni',array_diff($q_group_dnis, array("",0,null)));
        // }
        $workspace = get_current_workspace();

        $criteria_template = Criterion::select('id', 'name', 'field_id', 'code', 'multiple')
            ->with('field_type:id,name,code')
            ->whereRelation('workspaces', 'id', $workspace->id)
            ->where('is_default', INACTIVE)
            ->orderBy('name')
            ->get();

        foreach ($criteria_template as $i => $criterion) {
            $idx = $i;

            if ($request->has($criterion->code)) {
                $code = $criterion->code;
                $request_data = $request->$code;

                $query->join("criterion_value_user as cvu{$idx}", function ($join) use ($request_data, $idx) {

                    $request_data = is_array($request_data) ? $request_data : [$request_data];

                    $join->on('users.id', '=', "cvu{$idx}" . '.user_id')
                        ->whereIn("cvu{$idx}" . '.criterion_value_id', $request_data);
                });

            }
        }

        if($request->status){
            $status = $request->status;
            $list_status = self::getStatusCodes(['pending','in_review','observed','disapproved','passed']);
            $list_status = $list_status->whereIn('id',$status);
            if($list_status->where('code','pending')->first()){
                $query->whereDoesntHave('projects',function($q) use ($project){
                    $q->where('project_id',$project->id);
                })->orWhereHas('projects', function($q) use($status,$project){
                    $q->where('project_id',$project->id)->whereIn('status_id',$status);
                });
            }else{
                $query->whereHas('projects',function($q) use($status,$project){
                    $q->select('id')->where('project_id',$project->id)->whereIn('status_id',$status);
                });
            }
        }
        // dd($query->paginate($request->paginate)[0]);
        return $query->paginate($request->paginate);
    }


    protected function searchProjectUser($project){
        $user = auth()->user();
        // $extensions = config('constantes.extensiones');
        $project_search = $project->load('course:id,name');
        unset($project_search->updated_at);
        unset($project_search->created_at);
        unset($project_search->deleted_at);

        $project_resources = ProjectResources::where('from_resource','media_project_course')->where('project_id',$project->id)
                                ->select('type_id','type_media')
                                ->with(['media'=>function($q){
                                    return $q->select('id','title','file');
                                }])
                                ->get();
        $user_resources = ProjectResources::where('from_resource','media_project_user')
                            ->where('project_id',$project->id)
                            ->where('type_id',$user->id)    
                            ->select('path_file','filename as title','type_media as tipo','size')
                            ->get();

        $project_search->project_resources = count($project_resources)>0 ? $project_resources->pluck('media') : [];
        $project_search->user_resources = count($user_resources)>0 ? $user_resources : [];
        //status code
        $user_project = self::where('project_id',$project->id)->where('user_id',$user->id)->with('status')->select('id','status_id','msg_to_user')->first();
        $status_code = isset($user_project->status) ? $user_project->status->code : 'pending';
        $project_search->can_upload_files = in_array($status_code,['pending','in_review','observed']);
        $project_search->msg_to_user = $user_project ?  $user_project->msg_to_user : '' ;
        //constraint
        $project_search->constraints = config('project.constraints.user');
        return $project_search;
    }
    protected function downloadZipFiles($request){
        $data['rutas'] = $request->input('rutas');
        $usuario_tarea_id = $request->usuario_tarea_id;
        $usuario_tarea =  self::where('id',$usuario_tarea_id )
                        ->with(['usuario'=>function($q){
                            $q->select('id','dni');
                        },'tarea.curso'=>function($q){
                            $q->select('id','nombre');
                        }])->first();
        $curso_nombre = mb_strtolower(str_replace(' ', '-', $usuario_tarea->tarea->curso->nombre));
        $curso_nombre = str_replace('--', '-', $curso_nombre);
        $usuario_dni=trim($usuario_tarea->usuario->dni);
        $data['file_zip_name'] = $usuario_dni.'-'.$curso_nombre.'.zip';
        $name_zip = Media::createZipFromStorage($data);
        return $name_zip;
    }
    protected function updateUsuarioTarea($request){
        $usuario_tarea = $request->usuario_tarea;
        $usuario_tarea->status_id = $request->status_id; 
        $usuario_tarea->msg_to_user = $request->msg_to_user;
        $usuario_tarea->update();
    }
    //Guarda o actualiza la tarea
    protected function storeUpdateProjectUser($request){
        $project = $request->project;
        $user = $request->user;
        $status_code = $this->getStatusByCode('in_review');
        self::updateOrCreate(
            ['project_id'=>$project->id,'user_id'=>$user->id],
            ['project_id'=>$project->id,'user_id'=>$user->id,'status_id'=>$status_code->id
        ]);

        $project_resource = ProjectResources::storeUpdateRequest($request,$project,'media_project_user',false);      
    
        if(count($project_resource)==0){
            $status_code = $this->getStatusByCode('pending');
            self::where('project_id',$project->id)->where('user_id',$user->id)->update([
                'status_id'=>$status_code->id
            ]);
        }
        return $status_code->name;
    }
  
    private function projectsByUser($user,$with_course=false){
        // $helper = new HelperController();
        // $usuario_cursos = $helper->help_ids_cursos_x_criterios_v2($user->id);
        $courses_id = $user->getCurrentCourses(only_ids:true);
        $summary_courses = SummaryCourse::where('user_id',$user->id)->where('course_id',$courses_id)->whereRelation('status', 'code', 'aprobado')->get();
        $projects = Project::whereIn('course_id',$courses_id)->where('active',1)->select('id','course_id','indications');
        if($with_course){
            $projects = $projects->with(['course'=>function($q){
                $q->select('id','name','imagen');
            }]);
        }
        $statuses = Taxonomy::where('group', 'course')->where('type', 'user-status')->get();
        $projects = $projects->whereHas('course')->get()->map(function($project) use ($user,$statuses,$summary_courses,$with_course){
            $project->loadMissing('course:id');
            $course_status = Course::getCourseStatusByUser($user, $project->course, $summary_courses, [], $statuses);
            $project->available = $course_status['available'];
            if(!$with_course){
                unset($project->course);
            }
            unset($project->course->requirements);
            unset($project->course->topics);
            unset($project->course->polls);
            unset($project->course->summaries);
            return $project;
        });
        return $projects;
    }

    private function getStatusByCode($code)
    {
        $status = cache()->rememberForever('taxonomies_project_status_' . $code,  function () use ($code) {
            return Taxonomy::where('group', 'projects')
                        ->where('type', 'status')
                        ->where('code', $code)
                        ->where('active', 1)
                        ->first();
        });
        return $status;
    }
    protected function getStatusCodes($codes){
        $status = Taxonomy::select('id','code','name','position','group','description')->where('group', 'project')
        ->where('type', 'status')
        ->whereIn('code', $codes)
        ->where('active', 1)
        ->orderBy('position')
        ->get();
        return $status;
    }
}
