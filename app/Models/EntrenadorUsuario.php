<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EntrenadorUsuario extends Model
{
    protected $table = 'entrenadores_usuarios';

    protected $fillable = ['entrenador_id', 'usuario_id', 'estado'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // public function entrenador()
    // {
    //     return $this->belongsTo('App\Usuario', 'entrenador_id');
    // }

    /*======================================================= SCOPES ====================================================================*/

    public function scopeEstado($q, $estado)
    {
        return $q->where('estado', $estado);
    }

    public function scopeEntrenador($q, $entrenador_id)
    {
        return $q->where('entrenador_id', $entrenador_id);
    }

    public function scopeAlumno($q, $alumno_id)
    {
        return $q->where('usuario_id', $alumno_id);
    }

    /*==============================================================================================================================*/

    protected function gridEntrenadores($data)
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $queryEntrenadores = Usuario::where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ENTRENADOR);
        if (!empty($filtro) || $filtro == null)
            $queryEntrenadores->where(function ($query) use ($filtro) {
                $query->where('nombre', 'like', "%$filtro%");
                $query->orWhere('dni', 'like', "%$filtro%");
            });

        $field = request('sortBy') ?? 'created_at';
        $sort = request('sortDesc') == 'true' ? 'DESC' : 'ASC';
        
        $queryEntrenadores->orderBy($field, $sort);

        $entrenadores = $queryEntrenadores->paginate(request('paginate', 15));

        // info($entrenadores);
        // dd($entrenadores);

        $entrenador_usuario = EntrenadorUsuario::with('usuario')->whereIn('entrenador_id', $entrenadores->pluck('id')->all())->get();
        $responseData = [];
        foreach ($entrenadores->items() as $entrenador) {
            $alumnosEntrenador = $entrenador_usuario->where('entrenador_id', $entrenador->id)->where('estado', 1)->sortByDesc('updated_at');
            $tempAlumnos = collect();
            $alumnosEntrenador->each(function ($value, $key) use ($tempAlumnos) {
                $temp = [
                    'id' => $value->usuario->id,
                    'dni' => $value->usuario->dni,
                    'nombre' => $value->usuario->nombre,
                    'botica' => $value->usuario->botica,
                    'estado' => $value->estado === 1,
                    'loading' => false
                ];
                $tempAlumnos->push($temp);
            });
            $entrenador->alumnos = $tempAlumnos;
            $entrenador->alumnos_count = $entrenador->alumnos->count();
            $entrenador->asignar_ver_alumnos = false;
            $entrenador->asignar_alumnos = false;
            $responseData[] = $entrenador->only('dni', 'nombre', 'alumnos', 'asignar_ver_alumnos', 'botica', 'asignar_alumnos', 'config_id', 'id', 'alumnos_count');
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
        $entrenador = Usuario::where('dni', $usuario_dni)->first();
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


        $alumnos_ids = EntrenadorUsuario::where('entrenador_id', $entrenador['data_usuario']->id)->get();
        $queryDataAlumnos = Usuario::where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ALUMNO)->whereIn('id', $alumnos_ids->pluck('usuario_id')->all())
            ->select('id', 'nombre', 'dni', 'botica', DB::raw("CONCAT(dni, ' - ', nombre, ' - ', botica) as text"));

        if (!empty($filtro))
            $queryDataAlumnos->where(function ($query) use ($filtro) {
                $query->where('nombre', 'like', "%$filtro%");
                $query->orWhere('dni', 'like', "%$filtro%");
            });

        $dataAlumnos = $queryDataAlumnos->get();
        if ($dataAlumnos->count() == 0) {
            $response['error'] = true;
            $msg = 'El usuario no es un entrendor o no tiene usuarios con estado: ';
            $txt_estado = ($estado == 1) ? 'Activo' : 'Inactivo';
            $response['msg'] = $msg . $txt_estado;
        } else {
            $dataAlumnos->each(function ($value, $key) use ($alumnos_ids) {
                $value->estado = $alumnos_ids->where('usuario_id', $value->id)->first()->estado;
                $value->loading = false; // Modal Ver Alumnos : v-switch->value
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
        $alumnos_ids = EntrenadorUsuario::estado(1)->entrenador($entrenador['data_usuario']->id)->get();
        $queryDataAlumnos = Usuario::with([
            'matricula_presente.carrera' => function ($q) {
                $q->select('id', 'nombre');
            }
        ])->where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ALUMNO)
            ->whereIn('id', $alumnos_ids->pluck('usuario_id')->all())
            ->select('id', 'nombre', 'dni', 'botica', 'sexo');

        if (!empty($filtro)) {
            $queryDataAlumnos->where(function ($query) use ($filtro) {
                $query->where('nombre', 'like', "%$filtro%");
                $query->orWhere('dni', 'like', "%$filtro%");
            });
        }
        $dataAlumnos = $queryDataAlumnos->get();
        $dataAlumnos->each(function ($value, $key) use ($alumnos_ids, $entrenador) {
            $value->makeHidden('matricula_presente');
            $value->carrera = $value->matricula_presente->carrera->nombre;
        });
        $response['alumnos'] = $dataAlumnos;

        // TODO: Últimos 10 alumnos vistos
        $ultimos_alumnos_ids = ChecklistRpta::limit(10)->whereIn('alumno_id', $alumnos_ids->pluck('usuario_id'))->orderBy('updated_at', 'DESC')->groupBy('alumno_id')->get();
        $ultimos_alumnos = Usuario::with([
            'matricula_presente.carrera' => function ($q) {
                $q->select('id', 'nombre');
            }
        ])->where('rol_entrenamiento', Usuario::TAG_ROL_ENTRENAMIENTO_ALUMNO)
            ->whereIn('id', $ultimos_alumnos_ids->pluck('alumno_id')->all())
            ->select('id', 'nombre', 'dni', 'botica', 'sexo')
            ->get();

        $ultimos_alumnos->each(function ($value, $key) use ($alumnos_ids, $ultimos_alumnos_ids, $entrenador) {
            $value->makeHidden('matricula_presente');
            $value->carrera = $value->matricula_presente->carrera->nombre;
            $temp2 = $ultimos_alumnos_ids->where('alumno_id', $value->id)->where('entrenador_id', $entrenador['data_usuario']->id)->sortByDesc('updated_at')->first();
            if ($temp2) $value->ultima_actividad = $temp2->updated_at->format('Y-m-d H:i:s');

        });
        $response['ultimos_alumnos'] = $ultimos_alumnos;

        return $response;
    }

    protected function asignar($data)
    {
        $entrenador_id = $data['entrenador_id'];
        $usuario_id = $data['usuario_id'];
        $estado = $data['estado'];

        $response['error'] = false;

        $alumno = Usuario::where('id', $usuario_id)->first();
        if ($alumno->rol_entrenamiento === 'entrenador') {
            $response['error'] = true;
            $response['msg'] = 'El alumno que se quiere asignar, es un entrenador.';
            return $response;
        }

        $registro = EntrenadorUsuario::where('entrenador_id', $entrenador_id)
            ->where('usuario_id', $usuario_id)
            ->first();
        if (!$registro) {
            $response['error'] = true;
            $response['msg'] = 'No se puede asginar el usuario al entrenador con estado inactivo.';
            if ($estado == 1) {
                // TODO: Los alumnos deben estar definidos como tal antes de asignarle un entrenador o se debe asginarle el rol de alumno y luego asignarlo al entrenador?
                $alumno = Usuario::find($usuario_id);
                if ($alumno->rol_entrenamiento === null){
                    $alumno->rol_entrenamiento = Usuario::TAG_ROL_ENTRENAMIENTO_ALUMNO;
                    $alumno->save();
                }

                $response['error'] = false;
                $data['estado'] = 1;
                $data['estado_notificacion'] = 0;
                EntrenadorUsuario::create($data);
                $response['msg'] = 'Se asignó el usuario al entrenador.';
            }
        } else {
            $estado_txt = $estado == 1 ? 'activó' : 'inactivó';
            $registro->estado = $estado;
            $registro->estado_notificacion = $estado === 1 ? 0 : $registro->estado_notificacion;
            $registro->save();
            $msg = "Se $estado_txt la asignación existente del usuario y el entrenador.";
            $response['msg'] = $msg;
        }
        return $response;
    }

}
