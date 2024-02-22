<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Curso extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'config_id', 'categoria_id', 'duplicado_id', 'nombre', 'descripcion',
        'imagen', 'estado', 'orden', 'requisito_id', 'c_evaluable', 'libre',
        'reinicios_programado', 'plantilla_diploma',
        'duration', 'investment'
    ];

    public function temas()
    {
        return $this->hasMany(Posteo::class, 'curso_id');
    }

    public function config()
    {
        return $this->belongsTo(Abconfig::class, 'config_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function curso_perfiles()
    {
        return $this->hasMany(Curso_perfil::class, 'curso_id');
    }

    public function encuesta()
    {
        return $this->hasOne(Curso_encuesta::class, 'curso_id');
    }

    public function delete()
    {
        $this->temas()->delete();

        return parent::delete();
    }

    // CONSULTA REQUISITO DE UN POSTEO
    public function requisito()
    {
        return $this->belongsTo(Curso::class, 'requisito_id');
    }

    public function curricula()
    {
        return $this->hasMany(Curricula::class, 'curso_id');
    }

    public function update_usuarios()
    {
        return $this->hasMany(Update_usuarios::class, 'curso_id');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'relaciones_checklist', 'curso_id', 'checklist_id');
    }

    /*======================================================= GETTERS MUTATORS ==================================================================== */

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function getCodigoAttribute()
    {
        $codigo_matricula = $this->config->codigo_matricula ?? '';
        $code = $this->id;
        // $code = str_pad($this->id, 4, '0', STR_PAD_LEFT);

        return "{$codigo_matricula}-CUR-{$code}";
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeFiltroNombre($q, $filtro)
    {
        return $q->where('nombre', 'like', "%$filtro%");
    }

    public function scopeEstado($q, $estado)
    {
        return $q->where('estado', $estado);
    }
    /*=================================================================================================================================== */

    protected function getIdsPorModulo($modulo_id = NULL)
    {
        $cache_name = 'cursos_id_por_modulo';
        $cache_name .= $modulo_id ? "-{$modulo_id}" : '';

        $result = cache()->remember($cache_name, CACHE_SECONDS_DASHBOARD_GRAPHICS, function () use ($modulo_id) {

            return Curso::where('config_id', $modulo_id)->pluck('id')->toArray();
        });

        return $result;
    }

    protected function search($request, $paginate = 15)
    {
        $q = self::with([
            'update_usuarios' => function ($q_update) {
                $q_update->whereIn('estado', [0, 1]);
            }
        ])
            ->withCount('temas', 'encuesta')
            // ->orderBy('orden')
            ->where('categoria_id', $request->categoria_id);

        if ($request->q)
            $q->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }

    protected static function storeRequest($data, $curso = null)
    {
        try {

            DB::beginTransaction();

            if ($curso) :
                $curso->update($data);
            else :
                $data['libre'] = $data['categoria_modalidad'] === 'libre' ? 1 : 0;
                $curso = self::create($data);
            endif;

            $curso->save();
            DB::commit();
            return $curso;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    protected function validateCursoRequisito($data, $curso)
    {
        //        info($data);
        $requisitos = Curso::select('id', 'nombre', 'categoria_id')->where('requisito_id', $curso->id)->get();
        if (in_array($data['estado'], ['false', false, 0], true) && $requisitos->count() > 0) :
            //            info('EL CURSO ES REQUISITO DE OTRO');
            $validate = [];
            foreach ($requisitos as $req) {
                $route = route('cursos.editCurso', [$curso->config_id, $curso->categoria_id, $req->id]);
                $validate[] = "<a href='{$route}'>" . $req->nombre . "</a>";
            }
            return [
                'validate' => false,
                'data' => $validate,
                'type' => 'validate_curso_requisito',
                'title' => 'Ocurrió un problema'
            ];
        endif;
        //        info('EL CURSO NO ES REQUISITO DE OTRO');
        return ['validate' => true];
    }

    protected function moverCurso(Curso $curso, $escuela_id)
    {
        try {
            DB::beginTransaction();

            DB::table('cursos')->where('id', $curso->id)->update(
                [
                    'categoria_id' => $escuela_id,
                    'requisito_id' => null,
                ]
            );
            DB::table('posteos')->where('curso_id', $curso->id)->update(['categoria_id' => $escuela_id]);
            DB::table('ev_abiertas')->where('curso_id', $curso->id)->update(['categoria_id' => $escuela_id]);
            DB::table('pruebas')->where('curso_id', $curso->id)->update(['categoria_id' => $escuela_id]);
            DB::table('resumen_x_curso')->where('curso_id', $curso->id)->update(['categoria_id' => $escuela_id]);
            $categoria_nueva = Categoria::where('id', $escuela_id)->select('modalidad')->first();
            $libre = ($categoria_nueva->modalidad === 'libre') ? 1 : 0;
            self::find($curso->id)->update(['libre' => $libre]);

            DB::commit();
            return ['msg' => 'El curso se movió correctamente',];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['validate' => false, 'msg' => 'Ocurrió un problema.'];
        }
    }

    protected function validateMoverCurso(Curso $curso)
    {
        $cursos_requisitos = Curso::select('id', 'nombre')->where('requisito_id', $curso->id)->get();

        $validate = collect();

        if ($cursos_requisitos->count() > 0) :

            $validacion = $this->validateReqCursos($cursos_requisitos, $curso, 'mover');
            $validate->push($validacion);

        endif;

        if ($validate->count() === 0) return ['validate' => true];
        // Si existe algún tema que impida el envío de formulario (show_confirm = false)
        // no mostrar el botón de "Confirmar"
        $count = $validate->where('show_confirm', false)->count();

        return [
            'validate' => false,
            'data' => $validate->toArray(),
            'title' => 'Alerta',
            'show_confirm' => !($count > 0)
        ];
    }

    protected function validateUpdateStatus($curso, $estado)
    {
        $cursos_requisitos = Curso::select('id', 'nombre')->where('requisito_id', $curso->id)->get();
        $temas = $curso->temas;
        $hasActiveTema = $temas->where('estado', 1)->count() > 0;

        //        info("REQUISITOS COUNT :: " . $cursos_requisitos->count());
        //        info("TEMAS ACTIVOS:: " . $temas->count());
        //        info("ESTADO A ACTUALIZAR :: " . $estado);
        //        info("CURSO CONFIG ID :: " . $curso->config_id);

        if ($cursos_requisitos->count() > 0 || $hasActiveTema) :

            $validate = collect();

            if (!$hasActiveTema) {

                $validacion = $this->avisoAllTemasInactive($curso);
                $validate->push($validacion);
            }

            if ($cursos_requisitos->count() > 0) :

                $validacion = $this->validateReqCursos($cursos_requisitos, $curso);
                $validate->push($validacion);

            endif;

            if ($hasActiveTema) :

                $validacion = $this->validateHasActiveTema($curso, $temas);
                $validate->push($validacion);

            endif;

            if ($validate->count() === 0) return ['validate' => true];
            // Si existe algún tema que impida el envío de formulario (show_confirm = false)
            // no mostrar el botón de "Confirmar"
            $count = $validate->where('show_confirm', false)->count();

            return [
                'validate' => false,
                'data' => $validate->toArray(),
                'title' => 'Alerta',
                'show_confirm' => !($count > 0)
            ];

        endif;

        return ['validate' => true];
    }

    protected function validateCursoEliminar($curso)
    {
        $cursos_requisitos = Curso::select('id', 'nombre')->where('requisito_id', $curso->id)->get();
        $temas = $curso->temas;
        $hasActiveTema = $temas->where('estado', 1)->count() > 0;

        //        info("REQUISITOS COUNT :: " . $cursos_requisitos->count());
        //        info("TEMAS ACTIVOS:: " . $temas->count());
        //        info("ESTADO A ACTUALIZAR :: " . $estado);
        //        info("CURSO CONFIG ID :: " . $curso->config_id);

        if ($cursos_requisitos->count() > 0 || $hasActiveTema) :

            $validate = collect();

            if (!$hasActiveTema) {

                $validacion = $this->avisoAllTemasInactive($curso);
                $validate->push($validacion);
            }

            if ($cursos_requisitos->count() > 0) :

                $validacion = $this->validateReqCursos($cursos_requisitos, $curso, 'eliminar');
                $validate->push($validacion);

            endif;

            //            if ($hasActiveTema):
            //
            //                $validacion = $this->validateHasActiveTema($curso, $temas, 'eliminar');
            //                $validate->push($validacion);
            //
            //            endif;

            if ($validate->count() === 0) return ['validate' => true];
            // Si existe algún tema que impida el envío de formulario (show_confirm = false)
            // no mostrar el botón de "Confirmar"
            $count = $validate->where('show_confirm', false)->count();

            return [
                'validate' => false,
                'data' => $validate->toArray(),
                'title' => 'Alerta',
                'show_confirm' => !($count > 0)
            ];

        endif;

        return ['validate' => true];
    }


    public function validateReqCursos($req_cursos, $curso, $verb = 'desactivar')
    {
        $temp = [
            'title' => "No se puede {$verb} este curso.",
            'subtitle' => "Para poder {$verb} este curso es necesario quitar o cambiar el requisito en los siguientes cursos:",
            'show_confirm' => false,
            'type' => 'req_curso_validate'
        ];
        $list1 = [];

        foreach ($req_cursos as $req) {
            $route = route('cursos.editCurso', [$curso->config_id, $curso->categoria_id, $req->id]);
            $list1[] = "<a href='{$route}' target='_blank'>" . $req->nombre . "</a>";
        }

        $temp['list'] = $list1;
        return $temp;
    }

    public function validateHasActiveTema($curso, $temas, $verb = 'desactivar')
    {
        $temp = [
            'title' => "Tener en cuenta al desactivar el curso.",
            'subtitle' => "Los siguientes temas también se inactivarán:",
            'show_confirm' => true,
            'type' => 'has_active_tema'
        ];
        $list1 = [];
        foreach ($temas as $tema) {
            $route = route('temas.editTema', [$curso->config_id, $curso->categoria_id, $curso->id, $tema->id]);
            $list1[] = "<a href='{$route}' target='_blank'>" . $tema->nombre . "</a>";
        }
        $temp['list'] = $list1;
        return $temp;
    }

    public function avisoAllTemasInactive($curso)
    {
        $temp = [
            'title' => "Tener en cuenta",
            'subtitle' => null,
            'show_confirm' => true,
            'type' => 'all_temas_inactive_notice'
        ];
        $list[] = "Todos los temas del curso: <strong> {$curso->nombre}</strong>, se encuentra inactivos.";
        $temp['list'] = $list;
        return $temp;
    }

    protected function getMessagesActions($curso, $title = 'Curso actualizado con éxito')
    {

        $messages = [];
        //        dd($curso->wasChanged('estado'), $curso->estado, $curso->temas->count());
        if (($curso->wasChanged('active') || $curso->active) && $curso->topics->count() > 0) :
            $messages[] = [
                'title' => $title,
                'subtitle' => "Esto puede producir un ajuste en el avance de los usuarios. Los cambios se mostrarán en el app y web en unos minutos.",
                'type' => 'update_message'
            ];
        endif;

        return [
            'title' => 'Aviso',
            'data' => $messages
        ];
    }

    public function getActualizaciones()
    {
        $actualizaciones = [];
        if ($this->update_usuarios->count() > 0) {
            $parse_date = Carbon::parse($this->update_usuarios->last()->created_at)->addMinutes(25)->format('h:i A');
            $actualizaciones[] = "Este curso tiene {$this->update_usuarios->count()} actualizacion(es) pendiente. Este proceso terminará aproximadamente a las {$parse_date}";
        }

        return $actualizaciones;
    }
}
