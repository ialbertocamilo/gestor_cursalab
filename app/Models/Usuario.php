<?php

namespace App\Models;

// use App\Models\CriterionValueWorkspace;
use App\Models\Course;
use App\Models\Segment;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\UserResetPasswordNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Usuario extends Model
{
    protected $table = 'users';

    use Notifiable;

    const TAG_ROL_ENTRENAMIENTO_ENTRENADOR = 'entrenador';
    const TAG_ROL_ENTRENAMIENTO_ALUMNO = 'alumno';
    const TAG_ROL_ENTRENAMIENTO_NINGUNO = null;

    protected $fillable = [
        'config_id', 'grupo_id', 'nombre', 'cargo', 'estado', 'dni', 'email', 'botica_id',
        'password', 'estado_password', 'sexo', 'botica', 'grupo', 'token_firebase', 'grupo_nombre',
        'rol_entrenamiento'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * Route notifications for the Slack channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return config('slack.routes.support');
    }

    /**
     * obtener area asosiado a este usuaio
     */

    public function config()
    {
        return $this->belongsTo(Workspace::class, 'subworkspace_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Abconfig::class, 'config_id');
    }

    public function botica()
    {
        return $this->belongsTo(Botica::class, 'botica_id');
    }

    public function botica_relation()
    {
        return $this->belongsTo(Botica::class, 'botica_id');
    }

    /**
     * obtener area asosiado a este usuaio
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * obtener area asosiado a este usuaio
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function grupo_sistema()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    /**
     * obtener perfil asosiado a este usuaio
     */
    // public function perfil()
    // {
    //     return $this->belongsTo(Perfil::class, 'perfil_id');
    // }

    public function vigencia()
    {
        return $this->hasOne(Usuario_vigencia::class, 'usuario_id');
    }
    public function criterion_user()
    {
        return $this->hasMany(CriterionValueUser::class,'user_id');
    }
    //
    public function rpta_pruebas()
    {
        return $this->hasMany(Prueba::class, 'usuario_id');
    }

    public function rpta_encuestas()
    {
        return $this->hasMany(PollQuestionAnswer::class, 'usuario_id');
    }

    public function rpta_pruebas_dessaprob($config)
    {
        $mod_eval = json_decode($config->mod_evaluaciones, true);
        $pruebas_desap = $this->hasMany(Prueba::class, 'usuario_id')->where('resultado', 0)->where('intentos', '>=', $mod_eval['nro_intentos'])->first();
        if ($pruebas_desap) {
            return $pruebas_desap;
        }
        return;
    }

    // // Respuestas del TEMA seleccionado en adelante
    // public function rpta_pruebas()
    // {
    //     return $this->hasMany(Prueba::class, 'usuario_id');
    // }

    // // Respuestas del CURSO seleccionado en adelante
    // public function rpta_pruebas()
    // {
    //     return $this->hasMany(Prueba::class, 'usuario_id');
    // }

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'usuario_id');
    }

    public function matricula()
    {
        return $this->hasMany(Matricula::class, 'usuario_id');
    }

    // public function matricula_ordenada()
    // {
    //     return $this->hasMany(Matricula::class, 'usuario_id')
    //                 ->join('ciclos', 'matricula.ciclo_id', '=', 'ciclos.id')
    //                 ->where('matricula.estado', 1)
    //                 ->orderBy('ciclos.numeracion');
    // }

    public function matricula_ordenada()
    {
        return $this->where('estado', 1)->orderBy('secuencia_ciclo', 'DESC');
    }

    public function matricula_reciente()
    {
        return $this->hasMany(Matricula::class, 'usuario_id');
    }

    public function matricula_presente()
    {
        return $this->hasOne(Matricula::class, 'usuario_id')->where('presente', 1);
    }

    public function criterio()
    {
        return $this->belongsTo(Criterio::class, 'grupo');
    }

    public function actividad_evento()
    {
        return $this->hasOne(ActividadEvento::class, 'usuario_id');
    }

    public function pruebas()
    {
        return $this->hasMany(Prueba::class, 'usuario_id');
    }

    public function ev_abiertas()
    {
        return $this->hasMany(Ev_abierta::class, 'usuario_id');
    }

    public function encuestas_respuestas()
    {
        return $this->hasMany(PollQuestionAnswer::class, 'usuario_id');
    }

    public function diplomas()
    {
        return $this->hasMany(Diploma::class, 'usuario_id');
    }

    public function resumen_x_curso()
    {
        return $this->hasMany(Resumen_x_curso::class, 'usuario_id');
    }

    public function resumen_general()
    {
        return $this->hasOne(Resumen_general::class, 'usuario_id');
    }

    public function reporte_supervisor()
    {
        return $this->belongsToMany(Criterio::class, 'supervisores', 'usuario_id', 'criterio_id');
    }

    public function invitations()
    {
        return $this->hasMany(Attendant::class, 'usuario_id');
    }

    public function scopeDni($q, $dni)
    {
        return $q->where('dni', $dni);
    }

    public function scopeAsAttendantsWithToken($query, $attendant_ids)
    {
        return $query->whereHas('invitations', function ($q) use ($attendant_ids) {
            $q->whereIn('id', $attendant_ids ?? ['X']);
        })
            ->whereNotNull('token_firebase');
    }

    protected function getUsuariosExcluidosDeGraficos()
    {
        $cache_name = 'usuarios_excluidos_graficos';

        $result = cache()->remember($cache_name, CACHE_SECONDS_DASHBOARD_GRAPHICS, function () {

            $carreras = Carrera::select('id')
                                ->where('contar_en_graficos', 0)
                                ->pluck('id');

            return Matricula::select('usuario_id')
                            ->whereIn('carrera_id', $carreras)
                            ->pluck('usuario_id')
                            ->toArray();
        });

        return $result;
    }

    protected static function search($request)
    {
        $q = self::with('config');

        if ($request->q) {
            $q->where(function ($q_search) use ($request) {
                $q_search->where('nombre', 'like', "%$request->q%")
                    ->orWhere('dni', 'like', "%$request->q%");
            });
        }

        if ($request->module)
            $q->where('config_id', $request->module);

        if ($request->carrera):
            $q->whereHas('matricula_presente', function ($q_matricula) use ($request) {
                $q_matricula->where('carrera_id', $request->carrera);
                if ($request->ciclos)
                    $q_matricula->whereIn('ciclo_id', $request->ciclos);
            });
//            $q->whereHas('matricula', function ($q) use ($request) {
//                $q->where('carrera_id', $request->carrera);
//            });
        endif;


        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);
        // info($q->toSql());

        return $q->paginate($request->paginate);

    }

    /**
     * Get host users
     *
     * @param bool $idsOnly When true load users ids only
     * @return mixed
     */

    protected function getCurrentHostsIds() {

        return $this->getCurrentHosts(true,null,);
    }

    protected function getCurrentHosts($indexOnly = false, $workSpaceIdx = null,$type = 'users_full',$selects='')
    {
        $workSpaceIndex = $workSpaceIdx ?? get_current_workspace_indexes('id');

        # ==== criterios según segmentación (segmentacion directa). ====
        $currSegment = Workspace::find($workSpaceIndex)->segments;

        # ==== usuarios bajo criterio ====
        $course = new Course;
        $currUsers = $course->usersSegmented($currSegment, $type,[],$selects);

        return $indexOnly ? $currUsers->pluck('id')->toArray() : $currUsers;
    }
}
