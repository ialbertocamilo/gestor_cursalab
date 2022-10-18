<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Bouncer;

class EntrenadorUsuario extends Model
{
    protected $table = 'trainer_user';

    protected $fillable = ['trainer_id', 'user_id'];
    public $timestamps = false;
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
        return $q->where('user_id', $alumno_id);
    }

    /*==============================================================================================================================*/

    protected function gridEntrenadores($data)
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $queryEntrenadores = User::whereIs('trainer');

        // $queryEntrenadores = Usuario::where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ENTRENADOR);
        if (!empty($filtro) || $filtro == null)
            $queryEntrenadores->where(function ($query) use ($filtro) {
                $query->where('name', 'like', "%$filtro%");
                $query->orWhere('document', 'like', "%$filtro%");
            });

        $field = request('sortBy') ?? 'created_at';
        $sort = request('sortDesc') == 'true' ? 'DESC' : 'ASC';

        $queryEntrenadores->orderBy($field, $sort);

        $entrenadores = $queryEntrenadores->paginate(request('paginate', 15));

        // info($entrenadores);
        // dd($entrenadores->pluck('id')->all());

        $entrenador_usuario = EntrenadorUsuario::with('user')->whereIn('trainer_id', $entrenadores->pluck('id')->all())->get();
        $responseData = [];
        foreach ($entrenadores->items() as $entrenador) {
            $alumnosEntrenador = $entrenador_usuario->where('trainer_id', $entrenador->id)->sortByDesc('updated_at');
            $tempAlumnos = collect();
            $alumnosEntrenador->each(function ($value, $key) use ($tempAlumnos) {
                $subw = $value->user->subworkspace()->first();
                $temp = [
                    'id' => $value->user->id,
                    'document' => $value->user->document,
                    'name' => (!empty($value->user->fullname)) ? $value->user->fullname : $value->user->name,
                    'botica' => (!is_null($subw)) ? $subw->name : '',
                    'estado' => $value->active === 1,
                    'loading' => false
                ];
                $tempAlumnos->push($temp);
            });
            $entrenador->alumnos = $tempAlumnos;
            $entrenador->alumnos_count = $entrenador->alumnos->count();
            $entrenador->asignar_ver_alumnos = false;
            $entrenador->asignar_alumnos = false;
            $responseData[] = $entrenador->only('document', 'name', 'alumnos', 'asignar_ver_alumnos', 'botica', 'asignar_alumnos', 'config_id', 'id', 'alumnos_count');
        }

        $response['data'] = $responseData;
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

        $entrenador = $this->validarUsuario($entrenador_dni);
        if ($entrenador['error']) return $entrenador;


        $alumnos_ids = EntrenadorUsuario::where('trainer_id', $entrenador['data_usuario']->id)->get();
        $queryDataAlumnos = User::whereIn('users.id', $alumnos_ids->pluck('user_id')->all())
            ->select('users.id', 'users.name', 'users.document', 'users.subworkspace_id', DB::raw("CONCAT(users.document, ' - ', users.name) as text"));

        if (!empty($filtro))
            $queryDataAlumnos->where(function ($query) use ($filtro) {
                $query->where('users.name', 'like', "%$filtro%");
                $query->orWhere('users.document', 'like', "%$filtro%");
            });

        $dataAlumnos = $queryDataAlumnos->get();
        if ($dataAlumnos->count() == 0) {
            $response['error'] = true;
            $msg = 'El usuario no es un entrenador o no tiene usuarios con estado: ';
            $txt_estado = ($estado == 1) ? 'Activo' : 'Inactivo';
            $response['msg'] = $msg . $txt_estado;
        } else {
            $dataAlumnos->each(function ($value, $key) use ($alumnos_ids) {
                $subw = Workspace::where('id', $value->subworkspace_id)->first();

                $value->estado = $alumnos_ids->where('user_id', $value->id)->first()->estado;
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
        $filtro = $data['filtro'];

        $response['error'] = false;
        $response['alumnos'] = null;

        $entrenador = $this->validarUsuario($entrenador_dni);
        if ($entrenador['error']) {
            return $entrenador;
        }
        // TODO: Lista total de alumnos
        $alumnos_ids = EntrenadorUsuario::entrenador($entrenador['data_usuario']->id)->get();

        $queryDataAlumnos = User::leftJoin('workspaces as w', 'users.subworkspace_id', '=', 'w.id')
            ->whereIn('users.id', $alumnos_ids->pluck('user_id')->all())
            ->select('users.id', 'users.name', 'users.fullname as full_name', 'users.document', 'w.name as subworkspace');
        // $queryDataAlumnos = User::with([
        //     'matricula_presente.carrera' => function ($q) {
        //         $q->select('id', 'nombre');
        //     }
        // ])->whereIn('id', $alumnos_ids->pluck('user_id')->all())
        //     ->select('id', 'name', 'document', 'subworkspace_id');

        if (!empty($filtro)) {
            $queryDataAlumnos->where(function ($query) use ($filtro) {
                $query->where('users.name', 'like', "%$filtro%");
                $query->orWhere('users.document', 'like', "%$filtro%");
            });
        }
        $dataAlumnos = $queryDataAlumnos->get();

        $dataAlumnos->each(function ($value, $key) use ($alumnos_ids, $entrenador) {
            // $value->makeHidden('matricula_presente');
            $value->makeHidden(['abilities', 'roles', 'age', 'fullname']);
            // $value->carrera = $value->matricula_presente->carrera->nombre;
            $value->carrera = '';
        });
        $response['alumnos'] = $dataAlumnos;

        // TODO: Últimos 10 alumnos vistos
        $ultimos_alumnos_ids = ChecklistRpta::limit(10)->whereIn('student_id', $alumnos_ids->pluck('user_id'))->orderBy('updated_at', 'DESC')->groupBy('student_id')->get();
        $ultimos_alumnos = Usuario::leftJoin('workspaces as w', 'users.subworkspace_id', '=', 'w.id')
            ->whereIn('users.id', $ultimos_alumnos_ids->pluck('alumno_id')->all())
            ->select('users.id', 'users.name', 'users.fullname as full_name', 'users.document', 'w.name as subworkspace')
            ->get();
        // $ultimos_alumnos = Usuario::with([
        //     'matricula_presente.carrera' => function ($q) {
        //         $q->select('id', 'nombre');
        //     }
        // ])->where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ALUMNO)
        //     ->whereIn('id', $ultimos_alumnos_ids->pluck('alumno_id')->all())
        //     ->select('id', 'nombre', 'dni', 'botica', 'sexo')
        //     ->get();

        $ultimos_alumnos->each(function ($value, $key) use ($alumnos_ids, $ultimos_alumnos_ids, $entrenador) {
            // $value->makeHidden('matricula_presente');
            // $value->carrera = $value->matricula_presente->carrera->nombre;
            $value->carrera = '';
            $temp2 = $ultimos_alumnos_ids->where('student_id', $value->id)->where('trainer_id', $entrenador['data_usuario']->id)->sortByDesc('updated_at')->first();
            if ($temp2) $value->ultima_actividad = $temp2->updated_at->format('Y-m-d H:i:s');
        });
        $response['ultimos_alumnos'] = $ultimos_alumnos;

        return $response;
    }

    protected function asignar($data)
    {
        $trainer_id = $data['trainer_id'];
        $user_id = $data['user_id'];
        $estado = $data['active'];

        $response['error'] = false;

        $alumno = EntrenadorUsuario::where('trainer_id', $user_id)->first();
        if (!is_null($alumno)) {
            $response['error'] = true;
            $response['msg'] = 'El alumno que se quiere asignar, es un entrenador.';
            return $response;
        }

        $registro = EntrenadorUsuario::where('trainer_id', $trainer_id)
            ->where('user_id', $user_id)
            ->first();
        if (is_null($registro) || !$registro) {

            $data['trainer_id'] = $trainer_id;
            $data['user_id'] = $user_id;
            EntrenadorUsuario::create($data);

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
