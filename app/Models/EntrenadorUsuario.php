<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Bouncer;

use App\Traits\CustomAudit;
use Altek\Accountant\Contracts\Recordable;
class EntrenadorUsuario extends Model implements Recordable
{
    use \Altek\Accountant\Recordable, \Altek\Eventually\Eventually, CustomAudit;

    protected $table = 'trainer_user';

    protected $fillable = ['trainer_id', 'user_id', 'active'];

    public $defaultRelationships = [
        'user_id' => 'user',
        'trainer_id' => 'trainer',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    // public function entrenador()
    // {
    //     return $this->belongsTo('App\Usuario', 'trainer_id');
    // }

    /*======================================================= SCOPES ====================================================================*/

    public function scopeEntrenador($q, $trainer_id)
    {
        return $q->where('trainer_id', $trainer_id);
    }

    public function scopeAlumno($q, $alumno_id)
    {
        return is_array($alumno_id) ? $q->whereIn('user_id', $alumno_id) : $q->where('user_id', $alumno_id);
    }

    /*==============================================================================================================================*/

    protected function gridEntrenadores($data)
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';
//        $queryEntrenadores = User::whereIs('trainer');
        $queryEntrenadores = User::select('id','name','lastname','surname','fullname','document','subworkspace_id','active')->whereRelation('subworkspace', 'parent_id', $data['workspace_id'])
            ->whereHas('students');

        // $queryEntrenadores = Usuario::where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ENTRENADOR);
        if ((!empty($filtro) || $filtro == null) && !is_array($filtro)) {
            $queryEntrenadores->where(function ($query) use ($filtro) {
                $query->where('name', 'like', "%$filtro%");
                $query->orWhere('document', 'like', "%$filtro%");
            });
        }

        $field = request('sortBy') ?? 'created_at';
        $sort = request('sortDesc') == 'true' ? 'DESC' : 'ASC';

        $queryEntrenadores->orderBy($field, $sort);

        $entrenadores = $queryEntrenadores->withCount('students as alumnos_count')->paginate(request('paginate', 15));

        // info($entrenadores);

        // $entrenador_usuario = EntrenadorUsuario::with('user')->whereIn('trainer_id', $entrenadores->pluck('id')->all())->get();
        // $responseData = [];
        // foreach ($entrenadores->items() as $entrenador) {
        //     // $alumnosEntrenador = $entrenador_usuario->where('trainer_id', $entrenador->id)->sortByDesc('updated_at');
        //     $tempAlumnos = collect();
        //     // $alumnosEntrenador->each(function ($value, $key) use ($tempAlumnos) {
        //     //     $subw = $value->user->subworkspace()->first();
        //     //     $temp = [
        //     //         'id' => $value->user->id,
        //     //         'document' => $value->user->document,
        //     //         'name' => (!empty($value->user->fullname)) ? $value->user->fullname : $value->user->name,
        //     //         'botica' => (!is_null($subw)) ? $subw->name : '',
        //     //         'estado' => $value->active === 1,
        //     //         'loading' => false
        //     //     ];
        //     //     $tempAlumnos->push($temp);
        //     // });
        //     $entrenador->alumnos = $tempAlumnos;
        //     $entrenador->alumnos_count = $entrenador->alumnos->count();
        //     $entrenador->asignar_ver_alumnos = false;
        //     $entrenador->asignar_alumnos = false;
        //     $responseData[] = $entrenador->only('document', 'name', 'alumnos', 'asignar_ver_alumnos', 'botica', 'asignar_alumnos', 'config_id', 'id', 'alumnos_count');
        // }
        // dd($entrenadores->items());
        $response['data'] = $entrenadores->items();
        $response['lastPage'] = $entrenadores->lastPage();

        $response['current_page'] = $entrenadores->currentPage();
        $response['first_page_url'] = $entrenadores->url(1);
        $response['from'] = $entrenadores->firstItem();
        $response['last_page'] = $entrenadores->lastPage();
        $response['last_page_url'] = $entrenadores->url($entrenadores->lastPage());
        $response['next_page_url'] = $entrenadores->nextPageUrl();
        $response['path'] = $entrenadores->getOptions()['path'];
        $response['per_page'] = $entrenadores->perPage();
        $response['prev_page_url'] = $entrenadores->previousPageUrl();
        $response['to'] = $entrenadores->lastItem();
        $response['total'] = $entrenadores->total();

        return $response;
    }
    public static function listStudents($trainer_id){
        // $entrenador = EntrenadorUsuario::with(['student'=>function($q){
        //     $q->select('id','name','lastname','surname','fullname','document','subworkspace_id','active');
        // },'user.subworkspace'])->where('trainer_id', $trainer_id)->first();
        $students = EntrenadorUsuario::join('users','trainer_user.user_id','=','users.id')
                    ->where('trainer_user.trainer_id', $trainer_id)
                    ->select('users.id','name','lastname','surname','fullname','document','subworkspace_id','trainer_user.active as estado')
                    ->get();

        $workspace = get_current_workspace();
        $subworkspaces = Workspace::select('id','name')->where('parent_id',$workspace->id)->get();
        $tempAlumnos = collect();
        foreach ($students as $student) {
            $subw = $subworkspaces->where('id',$student->subworkspace_id)->first();
            $student->botica = (!is_null($subw)) ? $subw->name : '';
            $student->loading = false;
            // $temp = [
            //     'id' => $student->id,
            //     'document' => $student->document,
            //     'name' => (!empty($student->fullname)) ? $student->fullname : $student->name,
            //     'botica' => (!is_null($subw)) ? $subw->name : '',
            //     'estado' => $student->active === 1,
            //     'loading' => false
            // ];
            // $tempAlumnos->push($temp);
        }

        return [
            'alumnos'=> $students
        ];
    }
    public function validarUsuario($usuario_dni)
    {
        $response['error'] = false;
        $entrenador = User::where('document', $usuario_dni)->first();
        $response['data_usuario'] = $entrenador;
        if (!$entrenador) {
            $response['error'] = true;
            $response['msg'] = 'El usuario consultado no existe.';
            return $response;
        }

        return $response;
    }

    protected function getUsuariosByEntrenador($data)
    {
        $entrenador_dni = $data['entrenador_dni'];
        $filtro = $data['filtro'];
        $estado = 1;

        $response['error'] = false;
        $response['data'] = null;
        $response['pagination'] = [];

        $entrenador = $this->validarUsuario($entrenador_dni);
        if ($entrenador['error']) return $entrenador;



        $alumnos_ids = EntrenadorUsuario::where('trainer_id', $entrenador['data_usuario']->id)->get();
        $queryDataAlumnos = User::whereIn('users.id', $alumnos_ids->pluck('user_id')->all())
            ->select('users.id', 'users.name', 'users.lastname', 'users.surname', 'users.document', 'users.subworkspace_id', DB::raw("CONCAT(users.document, ' - ', users.name) as text"));

        if (!empty($filtro)) {
            $queryDataAlumnos->where(function ($query) use ($filtro) {
                $query->where('users.name', 'like', "%$filtro%");
                $query->orWhere('users.document', 'like', "%$filtro%");
            });
        }

        $dataAlumnos = $queryDataAlumnos->get();
        if ($dataAlumnos->count() == 0) {
            $response['error'] = true;
            $msg = 'El usuario no es un entrenador o no tiene usuarios con estado: ';
            $txt_estado = ($estado == 1) ? 'Activo' : 'Inactivo';
            $response['msg'] = $msg . $txt_estado;
        } else {
            $dataAlumnos->each(function ($value, $key) use ($alumnos_ids) {
                $subw = Workspace::where('id', $value->subworkspace_id)->first();
                $value->name = $value->fullname;
                $value->estado = $alumnos_ids->where('user_id', $value->id)->first()->active;
                $value->loading = false; // Modal Ver Alumnos : v-switch->value
                $value->botica = (!is_null($subw)) ? $subw->name : '';
            });
            $response['data'] = $dataAlumnos;
        }

        return $response;
    }

    protected function alumnosApi($data)
    {
        $entrenador_dni = $data['entrenador_dni'];
        $filtro_usuario = $data['filtro']['usuario'];
        $filtro_estado = $data['filtro']['estado'];

        $page = $data['page'];

        $response['error'] = false;
        $response['alumnos'] = null;

        $entrenador = $this->validarUsuario($entrenador_dni);
        if ($entrenador['error']) {
            return $entrenador;
        }
        // TODO: Lista total de alumnos
        $alumnos_ids = EntrenadorUsuario::entrenador($entrenador['data_usuario']->id)
            ->join('users', 'users.id', '=', 'trainer_user.user_id')
            ->where('users.active', 1)
            ->where('trainer_user.active', 1)
            ->get();
        $users_assigned = count($alumnos_ids);

        $queryDataAlumnos = User::leftJoin('workspaces as w', 'users.subworkspace_id', '=', 'w.id')
            ->leftJoin('summary_user_checklist as suc', 'suc.user_id', '=', 'users.id')
            ->when($filtro_estado, function($q) use ($filtro_estado){
               return   $filtro_estado == 'realizado'
                        ? $q->where('suc.advanced_percentage',100)
                        : $q->where(function ($q){
                            $q->where('suc.advanced_percentage', '!=', 100);
                            $q->orWhereNull('suc.advanced_percentage');
                        });
            })
            ->when($filtro_usuario, function($q) use ($filtro_usuario){
                $q->where(function ($query) use ($filtro_usuario) {
                    $query->where('users.name', 'like', "%$filtro_usuario%");
                    $query->orWhere('users.document', 'like', "%$filtro_usuario%");
                });
            })
            ->whereIn('users.id', $alumnos_ids->pluck('user_id')->all())
            ->select('users.id', 'users.name', 'users.lastname', 'users.surname', 'users.subworkspace_id','users.fullname as full_name', 'users.document', 'w.name as subworkspace','suc.advanced_percentage','suc.assigned');

        if ($page) {
            $perPage = 50;
            $pagination = $queryDataAlumnos
                ->paginate($perPage, ['*'], 'page', $page);

            $response['pagination'] = [
                'total' => $pagination->total(),
                'pages' => $pagination->lastPage(),
                'perPage' => $pagination->perPage(),
                'page' => $page,
                'users_assigned'=> $users_assigned
            ];

            $dataAlumnos = collect($pagination->items());
        } else {
            $dataAlumnos = $queryDataAlumnos->get();
        }

        $dataAlumnos->each(function ($value, $key) use ($alumnos_ids, $entrenador) {
            $value->makeHidden(['abilities', 'roles', 'age', 'fullname']);
            $value->carrera = '';
            $value->advanced_percentage = $value->advanced_percentage ?? 0;
            $value->assigned = $value->assigned ?? 0;
            $value->full_name = $value->getFullnameAttribute();
        });
        $response['alumnos'] = $dataAlumnos;
        $response['total_alumnos'] = count($dataAlumnos);

        return $response;
    }

    protected function asignar($data, $single_trainer = true)
    {
        $trainer_id = $data['trainer_id'];
        $user_id = $data['user_id'];
        $estado = $data['active'];

        $response['error'] = false;

        $alumno = EntrenadorUsuario::where('trainer_id', $user_id)->first();
        if (!is_null($alumno)) {
            $alumno_doc = User::select('document')->where('id', $user_id)->where('active', 1)->first();
            $alumno_doc = $alumno_doc?->document ?? $user_id;
            $response['error'] = true;
            $response['msg'] = 'El usuario con Doc. de Identidad ('.$alumno_doc.'), es un entrenador.';
            return $response;
        }

        $is_trainer = EntrenadorUsuario::where('user_id', $trainer_id)->first();
        if (!is_null($is_trainer)) {
            $response['error'] = true;
            $response['msg'] = 'El entrenador que se quiere asignar, es un alumno.';
            return $response;
        }
        $entrenador = User::where('id', $trainer_id)->where('active', 1)->first();
        $entrenador_workspace = Workspace::select('parent_id')->where('id', $entrenador->subworkspace_id)->first();
        if (is_null($entrenador_workspace)) {
            $entrenador_workspace = AssignedRole::getUserAssignedRoles($entrenador->id)->first();
            $entrenador_workspace_id = $entrenador_workspace->scope;
        } else {
            $entrenador_workspace_id = $entrenador_workspace->parent_id;
        }
        Bouncer::scope()->to($entrenador_workspace_id);
        Bouncer::assign('trainer')->to($entrenador);

        if($single_trainer) {
            $hasTrainer = EntrenadorUsuario::where('user_id', $user_id)->where('active',1)->first();
            if (!is_null($hasTrainer)) {
                $hasTrainer->trainer_id = $trainer_id;
                $hasTrainer->save();
                //update relations
                ChecklistRpta::alumno($user_id)->update([
                    'coach_id'=>$trainer_id
                ]);
                // $userTrainer = User::select('document')->where('id', $hasTrainer->trainer_id)->where('active', 1)->select('document')->first();
                // $response['error'] = true;
                // $response['msg'] = 'El usuario esta asignado al entrenador con documento: '.$userTrainer->document;
                // return $response;
                $msg = "Se asignó el usuario y el entrenador.";
                $response['msg'] = $msg;
                return $response;
            }
        }

        $registro = EntrenadorUsuario::where('trainer_id', $trainer_id)
            ->where('user_id', $user_id)
            ->first();
        if (is_null($registro) || !$registro) {
            $data['trainer_id'] = $trainer_id;
            $data['user_id'] = $user_id;
            EntrenadorUsuario::create($data);
            $response['error'] = false;
            $response['msg'] = 'Se asignó el usuario al entrenador.';
        } else {
            $registro->save();
            $msg = "Se asignó el usuario y el entrenador.";
            $response['msg'] = $msg;
        }


        return $response;
    }
}
