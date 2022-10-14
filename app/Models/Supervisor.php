<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\getUsuariosFromExcelImport;

class Supervisor extends User
{
    public function supervisor()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function criterion_value()
    {
        return $this->belongsTo(CriterionValue::class, 'criterion_value_id');
    }

    static function searchSupervisor($request)
    {
        $query = User::query()
//            ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'dni', 'config_id')
//            ->where('is_supervisor', 1)
            ->withCount(['supervisor_data as criterios_count' => function ($q) {
                $q->where('type', 'criterios');
            }, 'supervisor_data as usuarios_count' => function ($q) {
                $q->where('type', 'dni');
            }])
            ->whereIn('config_id', $modules->pluck('id'));
        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->whereRaw('dni like ?', ["%$request->q%"]);
                $q->orWhereRaw('nombre like ?', ["%$request->q%"]);
                $q->orWhereRaw('apellido_paterno like ?', ["%$request->q%"]);
                $q->orWhereRaw('apellido_materno like ?', ["%$request->q%"]);
            });
        }
        if ($request->modulos) {
            $query->whereIn('config_id', $request->modulos);
        }
        return $query->paginate($request->paginate);
    }

    static function subirExcelUsuarios($request)
    {
        $info = [];
        if ($request->hasFile("archivo")) {
            $import = new getUsuariosFromExcelImport();
            Excel::import($import, $request->file('archivo'));
            $info = $import->get_data();
            return [
                'error' => false,
                'info' => $info
            ];
        }
        return [
            'error' => true,
            'msg' => 'No se ha seleccionado ningún archivo',
            'info' => []
        ];
    }

    static function searchUsuario($request)
    {
        //Solo de los módulos a los que tiene permiso el administrador
        $modules = Abconfig::getModulesUser();

        $filtro = $request->filtro;
        $usuarios_filtrados = isset($request->config_id) ? Usuario::where('config_id', $request->config_id) : new Usuario();
        $usuarios_filtrados = $usuarios_filtrados->where(function ($query) use ($filtro) {
            $query->whereRaw('dni like ?', ["%$filtro%"]);
            $query->orWhereRaw('nombre like ?', ["%$filtro%"]);
            $query->orWhereRaw('apellido_paterno like ?', ["%$filtro%"]);
            $query->orWhereRaw('apellido_materno like ?', ["%$filtro%"]);
        })
            ->select(
                DB::raw(" CONCAT(dni,' - ',nombre, ', ', apellido_paterno, ' ', apellido_materno) as nombre"),
                'dni', 'is_supervisor')
            ->limit(40)
            ->whereIn('config_id', $modules->pluck('id'))
            ->get();
        $usuarios_filtrados->map(function ($usuario) {
            if ($usuario->is_supervisor) {
                $usuario->nombre = $usuario->nombre . ' (es supervisor)';
            }
        });
        return $usuarios_filtrados;
    }

    static function setUsuariosAsSupervisor($usuarios)
    {
        $c_usuarios = collect($usuarios);
        Usuario::whereIn('dni', $c_usuarios->pluck('dni'))->update([
            'is_supervisor' => 1
        ]);
    }

    static function searchSupervisores($request)
    {
        $filtro = $request->filtro;
        $usuarios_filtrados = Usuario::where('is_supervisor', 1);
        //Solo de los módulos a los que tiene permiso el administrador
        $modules = Abconfig::getModulesUser();
        if ($filtro) {
            $usuarios_filtrados = $usuarios_filtrados->where(function ($query) use ($filtro) {
                $query->whereRaw('dni like ?', ["%$filtro%"]);
                $query->orWhereRaw('nombre like ?', ["%$filtro%"]);
                $query->orWhereRaw('apellido_paterno like ?', ["%$filtro%"]);
                $query->orWhereRaw('apellido_materno like ?', ["%$filtro%"]);
            });
        }
        return $usuarios_filtrados->whereIn('config_id', $modules->pluck('id'))->select(
            DB::raw(" CONCAT(dni,' - ',nombre, ', ', apellido_paterno, ' ', apellido_materno) as nombre"),
            'dni', 'is_supervisor')
            ->get();
    }

    static function setCriterioGlobalesSupervisor($users_id, $criteria)
    {
        $users = User::query()
            ->with([
                'criterion_values' => function ($q) use ($criteria) {
                    $q->whereIn('criterion_id', $criteria);
                }
            ])
            ->whereIn('id', $users_id)
            ->select('id', 'name', 'lastname', 'surname', 'document')
            ->get();

        SegmentValue::whereHas('segment', function ($q) use ($users) {
            $q->where('model_type', User::class)
                ->whereIn('model_id', $users->pluck('id')->toArray())
                ->whereRelation('type', 'code', 'direct-segmentation')
                ->whereRelation('code', 'code', 'user-supervise');
        })
            ->delete();
        Segment::where('model_type', User::class)
            ->whereIn('model_id', $users->pluck('id')->toArray())
            ->whereRelation('type', 'code', 'direct-segmentation')
            ->whereRelation('code', 'code', 'user-supervise')
            ->delete();

        $direct_segmentation_type = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
        $supervise_code_segment = Taxonomy::getFirstData('segment', 'code', 'user-supervise');
        $errors = [];
        $ok = [];

        foreach ($users as $user) {
            $segment = Segment::firstOrCreate([
                'name' => UserRelationship::SUPERVISOR_DIRECT_SEGMENTATION_NAME,
                'model_type' => User::class,
                'model_id' => $user->id,
                'active' => ACTIVE,
                'type_id' => $direct_segmentation_type?->id,
                'code_id' => $supervise_code_segment?->id
            ]);

            $values = [];

            foreach ($criteria as $criterion) {
                $user_criterion_value = $user->criterion_values
                    ->where('criterion_id', $criterion)->first();

                if ($user_criterion_value)
                    $values[] = [
                        'criterion_value_id' => $user_criterion_value->id,
                        'criterion_id' => $criterion,
                        'type_id' => null,
                    ];
            }

            if (count($values) === 0):
                $errors[] = $user;
            else:
                $ok[] = $user;
                $segment->values()->sync($values);
            endif;
        }

        return compact('ok', 'errors');
    }

    static function setDataSupervisor($request)
    {
        $type = $request['type'];
        $supervisor = $request['supervisor'];
        self::where('usuario_id', $supervisor)->where('type', $type)->delete();
        $resources = [];
        switch ($type) {
            case 'dni':
                $resources = Usuario::whereIn('dni', collect($request['resources'])->pluck('dni'))->select('id')->get();
                break;
            case 'criterios':
                $resources = TipoCriterio::whereIn('id', $request['resources'])->select('id')->get();
                break;
        }
        $data_supervisor = collect();
        foreach ($resources as $resource) {
            $data_supervisor->push([
                'usuario_id' => $supervisor,
                'type' => $type,
                'resource_id' => $resource['id']
            ]);
        }
        if ($data_supervisor->count() > 0) {
            Supervisor::insert($data_supervisor->toArray());
        }
    }

    static function deleteSupervisor($supervisor)
    {
        Usuario::where('id', $supervisor)->update([
            'is_supervisor' => 0
        ]);
    }

    static function getData($supervisor, $type)
    {
        $supervisor_data = self::where('usuario_id', $supervisor)->where('type', $type)->get();
        $data = [];
        switch ($type) {
            case 'dni':
                $data = Usuario::whereIn('id', $supervisor_data->pluck('resource_id'))
                    ->select(
                        DB::raw(" CONCAT(dni,' - ',nombre, ', ', apellido_paterno, ' ', apellido_materno) as nombre"),
                        'dni', 'is_supervisor')
                    ->get();
                break;
            case 'criterios':
                $data = TipoCriterio::whereIn('id', $supervisor_data->pluck('resource_id'))->select('id')->get();
                break;
        }
        return $data;
    }

    static function tipoCriterios()
    {
        return TipoCriterio::select('id', 'nombre')->where('data_type', '<>', 'Fecha')->get();
    }

    static function modulos()
    {
        $modulos = Abconfig::getModulesUser()->map(function ($modulo) {
            $modulo->nombre = $modulo->etapa;
            return $modulo;
        });
        return $modulos;

    }

    //Funciones para las apis de la aplicación
    static function getInitData($usuario_supervisor)
    {
        $usuarios_id_x_criterios = Supervisor::usuariosIdXCriterios($usuario_supervisor);
        $usuarios_id_x_dni = Supervisor::usuariosIdByDNI($usuario_supervisor);
        $usuarios_id = $usuarios_id_x_criterios->merge($usuarios_id_x_dni)->unique();
        $usuarios = Usuario::with([
            'resumen_x_curso' => function ($res_x_curso) {
                $res_x_curso->select('id', 'curso_id', 'usuario_id', 'categoria_id', 'estado')->where('estado_rxc', 1);
            }
        ])
            ->select('id', 'nombre', 'dni', 'config_id')
            ->whereIn('id', $usuarios_id)
            ->where('estado', 1)
            ->where('rol', 'default')
            ->withCount('usuario_cursos')
            ->get();
        $CA = 0; // aprobados
        $CD = 0; // desaprobados
        $CED = 0; // en desarrollo
        $CEP = 0; // encuesta pendiente
        $CP = 0; // pendiente
        foreach ($usuarios as $usuario) {
            $cursos_asignados = $usuario->usuario_cursos_count;
            $CP += $cursos_asignados - $usuario->resumen_x_curso->where('estado', '<>', 'pendiente')->count();
            $CA += $usuario->resumen_x_curso->where('estado', 'aprobado')->count();
            $CD += $usuario->resumen_x_curso->where('estado', 'desaprobado')->count();
            $CED += $usuario->resumen_x_curso->where('estado', 'desarrollo')->count();
            $CEP += $usuario->resumen_x_curso->where('estado', 'enc_pend')->count();
        }
        return [
            'reportes' => [
                [
                    'base_url' => env('MIX_API_REPORTES_EXPORTAR'),
                    'id' => 'supervisores_avance_curricula',
                    'nombre' => 'Avance de currícula'
                ], [
                    'base_url' => env('MIX_API_REPORTES_EXPORTAR'),
                    'id' => 'supervisores_notas',
                    'nombre' => 'Notas por Cursos'
                ]
            ],
            'estados' => [
                [
                    'id' => 'aprobado',
                    'nombre' => 'Completado',
                ],
                [
                    'id' => 'desaprobado',
                    'nombre' => 'Desaprobado',
                ],
                [
                    'id' => 'desarrollo',
                    'nombre' => 'Desarrollo',
                ],
                [
                    'id' => 'enc_pend',
                    'nombre' => 'Encuesta pendiente',
                ],
                [
                    'id' => 'pendiente',
                    'nombre' => 'Pendiente'
                ]
            ],
            'usuarios_activos' => $usuarios->count(),
            'completados' => $CA,
            'desaprobados' => $CD,
            'desarrollo' => $CED,
            'encuesta_pendiente' => $CEP,
            'pendientes' => $CP,
        ];
    }

    static function getEscuelasBySupervisor($usuario_supervisor)
    {
        $categorias_id = Curso::select('categoria_id')->whereIn('id', self::cursosIdXUsuarios($usuario_supervisor))->pluck('categoria_id');
        $escuelas = Categoria::whereIn('id', $categorias_id)
            ->select('id', 'nombre')
            ->where('estado', 1)
            ->get();
        return $escuelas;
    }

    static function getCursosBySupervisor($usuario_supervisor, $escuelas_id)
    {
        $cursos = Curso::whereIn('categoria_id', $escuelas_id)->whereIn('id', self::cursosIdXUsuarios($usuario_supervisor))
            ->select('id', 'nombre')
            ->get();
        return $cursos;
    }

    static function cursosIdXUsuarios($usuario_supervisor)
    {
        $usuarios_id_x_criterios = Supervisor::usuariosIdXCriterios($usuario_supervisor);
        $usuarios_id_x_dni = Supervisor::usuariosIdByDNI($usuario_supervisor);
        $usuarios_id = $usuarios_id_x_criterios->merge($usuarios_id_x_dni);
        return UsuarioCurso::whereHas('curso', function ($q) {
            $q->where('estado', 1);
        })->whereIn('usuario_id', $usuarios_id)
            ->groupBy('curso_id')
            ->select('curso_id')
            ->pluck('curso_id');

    }

    static function usuariosIdByDNI($usuario_supervisor)
    {
        $usuario_supervisor_dni = Supervisor::where('usuario_id', $usuario_supervisor->id)->where('type', 'dni')->select('resource_id')->pluck('resource_id');
        return Usuario::select('id')->whereIn('id', $usuario_supervisor_dni)->where('estado', 1)->where('rol', 'default')->pluck('id');
    }

    static function usuariosIdXCriterios($usuario_supervisor)
    {
        $usuario_supervisor_tipo_criterios = Supervisor::where('usuario_id', $usuario_supervisor->id)->where('type', 'criterios')->select('resource_id')->pluck('resource_id');
        $usuario_supervisor_criterios = UsuarioCriterio::where('usuario_id', $usuario_supervisor->id)->whereHas('criterio', function ($q) use ($usuario_supervisor_tipo_criterios) {
            $q->whereIn('tipo_criterio_id', $usuario_supervisor_tipo_criterios);
        })->pluck('criterio_id');
        //Obtener todos los usuarios que coincidan con los criterios del usuario_supervisor
        // SELECT count(usuario_id) as count_usuarios FROM `usuario_criterios` where criterio_id in (4352, 11752) GROUP by usuario_id HAVING count_usuarios > 1
        $usuarios_id = UsuarioCriterio::select('usuario_id', DB::raw('count(usuario_id) as count_usuarios'))
            ->whereHas('usuario', function ($q) use ($usuario_supervisor) {
                $q->where('config_id', $usuario_supervisor->config_id)->where('estado', 1)->where('rol', 'default');
            })
            ->whereIn('criterio_id', $usuario_supervisor_criterios)
            ->groupBy('usuario_id')
            ->having('count_usuarios', '=', count($usuario_supervisor_criterios))
            ->pluck('usuario_id');
        return $usuarios_id;
    }

}
