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
        $data['count_aprobados'] = 0;
        $data['count_pendientes'] = 0;
        $user = auth()->user();
        $tareas = $this->tareasByUser($user);
        if(count($tareas)>0){
            $status_id_completed = self::getStatusCodes(['disapproved','passed']);
            $data['count_aprobados'] = self::whereIn('tarea_id',$tareas->pluck('id'))
                                    ->where('usuario_id',$user->id)
                                    ->whereIn('status_id',$status_id_completed->pluck('id'))
                                    ->count();
            $data['count_pendientes'] = $tareas->count() - $data['count_aprobados'];
        }
        $data['recommendations'] = config('tarea.recommendations');
        return $data;
    }
    protected function userTareas($type){
        $user = auth()->user();
        $tareas = $this->tareasByUser($user,true);
        $tareas_response=[];
        if(count($tareas)>0){
            $list_status = ($type=='pendiente') ? ['pending','in_review','observed'] : ['disapproved','passed'];
            $status =  self::getStatusCodes($list_status);

            $usuario_tarea = self::whereIn('tarea_id',$tareas->pluck('id'))
                            ->with(['status'=>function($q){
                                $q->select('id','name','code');
                            }])
                            ->where('usuario_id',$user->id)
                            ->get();
            foreach ($tareas as $tarea) {
                $find_usuario_tarea = $usuario_tarea->where('tarea_id',$tarea->id)->first();
                $tarea->estado_code = 'pending';
                $tarea->estado_label = 'Pendiente';
                if(isset($find_usuario_tarea->status)){
                    $tarea->estado_code = $find_usuario_tarea->status->code;
                    $tarea->estado_label = $find_usuario_tarea->status->name;
                }
                
                in_array($tarea->estado_code,$list_status) && ($tareas_response[]=$tarea);
            }
        }
       
        return $tareas_response;
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


    protected function searchTareaUser($tarea){
        $user = auth()->user();
        // $extensions = config('constantes.extensiones');
        $tarea_search = Project::with(['curso'=>function($q){
            return $q->select('id','nombre','config_id','categoria_id');
        }]
        )->where('id',$tarea->id)->select('id','curso_id','indications','active')->first();
        $tarea_resources = TareaResources::where('from_resource','media_tarea_curso')->where('tarea_id',$tarea->id)
                                ->select('type_id','type_media')
                                ->with(['media'=>function($q){
                                    return $q->select('id','title','file');
                                }])
                                ->get()->map(function($tarea_resource){
                                    $tarea_resource->media->tipo = $tarea_resource->type_media;
                                    $file = $tarea_resource->media->file;
                                    $tarea_resource->media->path =  str_contains($file,'http') ? $file : env('DO_URL') . "/" .$file ;
                                    return  $tarea_resource;
                                });
        $user_resources = TareaResources::where('from_resource','media_project_user')
                            ->where('tarea_id',$tarea->id)
                            ->where('type_id',$user->id)    
                            ->select('path_file','filename as title','type_media as tipo','size')
                            ->get()->map(function($user_resource){
                                $file = $user_resource->path_file;
                                $user_resource->type_resource = 'media';
                                $user_resource->relative_path = $file;
                                $user_resource->path_file =  str_contains($file,'http') ? $file : env('DO_URL') . "/" .$file;
                                return $user_resource;
                            });

        $tarea_search->tarea_resources = count($tarea_resources)>0 ? $tarea_resources->pluck('media') : [];
        $tarea_search->user_resources = count($user_resources)>0 ? $user_resources : [];
        //CODE
        $usuario_tarea = self::where('tarea_id',$tarea->id)->where('usuario_id',$user->id)->with('status')->select('id','status_id','msg_to_user')->first();
        $estado_code = isset($usuario_tarea->status) ? $usuario_tarea->status->code : 'pending';
        $tarea_search->can_upload_files = in_array($estado_code,['pending','in_review','observed']);
        $tarea_search->msg_to_user = $usuario_tarea ?  $usuario_tarea->msg_to_user : '' ;
        //constraint
        $tarea_search->constraints = config('tarea.constraints.user');
        return $tarea_search;
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
    protected function storeUpdateUsuarioTarea($request){
        $tarea = $request->tarea;
        $usuario = $request->user;
        $status_code = $this->getStatusByCode('in_review');
        self::updateOrCreate(
            ['tarea_id'=>$tarea->id,'usuario_id'=>$usuario->id],
            ['tarea_id'=>$tarea->id,'usuario_id'=>$usuario->id,'status_id'=>$status_code->id
        ]);

        $tarea_resource = TareaResources::storeUpdateRequest($request,$tarea,'media_project_user',false);      
    
        if(count($tarea_resource)==0){
            $status_code = $this->getStatusByCode('pending');
            self::where('tarea_id',$tarea->id)->where('usuario_id',$usuario->id)->update([
                'status_id'=>$status_code->id
            ]);
        }
        return $status_code->name;
    }
  
    private function tareasByUser($user,$with_course=false){
        $helper = new HelperController();
        $usuario_cursos = $helper->help_ids_cursos_x_criterios_v2($user->id);
        $tareas = Project::whereIn('curso_id',$usuario_cursos)->where('active',1)->select('id','curso_id','indications');
        if($with_course){
            $tareas = $tareas->with(['curso'=>function($q){
                $q->select('id','nombre','categoria_id as escuela_id','imagen');
            }]);
        }
        $tareas =$tareas->whereHas('curso')->get();
        if($with_course){
            $tareas->map(function($tarea){
                if(!str_contains($tarea->curso->image,'http')){
                    $tarea->curso->imagen = env('DO_URL'). "/".$tarea->curso->imagen;
                } 
                return $tarea;
            });
        }
        return $tareas;
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
        $status = Taxonomy::select('id','code','name','position','group','description')->where('group', 'tareas')
        ->where('type', 'status')
        ->whereIn('code', $codes)
        ->where('active', 1)
        ->orderBy('position')
        ->get();
        return $status;
    }
}
