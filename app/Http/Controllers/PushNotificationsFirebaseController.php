<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificacionPush\NotificacionPushSearchResource;
use App\Models\Abconfig;
use App\Models\ActividadEvento;
use App\Models\Criterion;
use App\Models\Eventos;
use App\Models\Matricula;
use App\Models\PushNotification;
use App\Models\User;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationsFirebaseController extends Controller
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        // Initialize locale

        Carbon::setLocale('es');
    }

    /**
     * Return main view
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $data = array();
        return view('notificaciones_push.index', $data);
    }


    /**
     * Get items list for select inputs
     *
     * @return JsonResponse
     */
    public function getListSelects()
    {
        $estados = [];
        $modules = Criterion::getValuesForSelect('module');

        foreach ($modules as $module) {
            $module->modulo_selected = false;
            $module->carreras_selected = false;
            $module->carreras = Criterion::getValuesForSelect('position_name');
        }

        return $this->success(get_defined_vars());
    }

    /**
     * Submit push notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enviarNotificacionCustom(Request $request)
    {
//        dd($request->all());
        $destinatarios = json_decode($request->destinatarios);
        $_destinatarios = collect();

        // todo: when replacement for Matricula table is defined
        foreach ($destinatarios as $mod) {

            foreach ($mod->carreras as $carr) {
//                dd($carr->carrera_id);
                $users = User::whereHas('criterion_values', function ($q) use ($carr) {
                    $q->whereIn('id', [$carr->carrera_id]);
                })
                    ->pluck('id');

//                $usuarios_matriculas = Matricula::where('carrera_id', $carr->carrera_id)
//                                                ->where('presente', 1)
//                                                ->get(['usuario_id']);
//
                foreach ($users as $us)
                    $_destinatarios->push($us);

            }
        }

        // Calculates notification time

        $push_chunk = 500;
        $envio_inicio = 2;
        $intervalo = 15;
        $_detalles_json = collect();
        $i = 1;
        $chunk = array_chunk($_destinatarios->unique()->values()->all(), $push_chunk);
//        $chunk = array_chunk($_destinatarios->all(), $push_chunk);
//        dd($chunk);
        $ahora = now();
        $ahora->addMinutes($envio_inicio);

        foreach ($chunk as $key => $agrupado) {

            $_detalles_json->push([
                'chunk' => $i,
                'hora_envio' => $ahora->format('Y-m-d H:i'),
                'estado_envio' => 0,
                'usuarios' => $agrupado
            ]);

            $ahora->addMinutes($intervalo);
            $i++;
        }

        // Save notification in database

        $nueva_notificacion = new PushNotification();
        $nueva_notificacion->titulo = $request->titulo;
        $nueva_notificacion->texto = $request->texto;
        $nueva_notificacion->destinatarios = $request->destinatarios;
        $nueva_notificacion->creador_id = $request->creador_id;
        $nueva_notificacion->estado_envio = 1; // PENDIENTE (aún no iniciado)
        $nueva_notificacion->detalles_json = json_encode($_detalles_json);
        $nueva_notificacion->success = 0;
        $nueva_notificacion->failure = 0;
        $nueva_notificacion->save();

        return response()->json([
                'error' => false,
                'title' => 'Notificación registrada',
                'msg' => "Se enviará durante los próximos minutos."
            ]
            , 200
        );
    }


    public function enviarNotificacionFormularioEvento($evento_id)
    {
        $evento = Eventos::where('id', $evento_id)->first();
        $asistentes_evento = ActividadEvento::where('evento_id', $evento->id)
            ->where('estado', 1)
            ->pluck('usuario_id');
        $array_tokens = Usuario::whereIn('id', $asistentes_evento)->whereNotNull('token_firebase')->pluck('token_firebase');
//        info($array_tokens);

        $data_notificacion = array(
            'tipo' => 'notificacion_formulario',
            'link' => $evento->link_google_form
        );

        $titulo = $evento->titulo;
        $body = 'Haz click aquí para ir al formulario.';

        try {

            $resultado = PushNotification::enviar(
                $titulo, $body, $array_tokens, $data_notificacion
            );

            return response()->json([
                'error' => false,
                'msg' => 'Notificaciones enviadas correctamente.'
            ], 200);

        } catch (\Throwable $th) {

//            info($th);
            return response()->json([
                'error' => true,
                'msg' => 'Error en el servidor, intente en un minuto.'
            ], 200);
        }
    }

    public function search(Request $request)
    {
        $notificaciones = PushNotification::search($request);

        NotificacionPushSearchResource::collection($notificaciones);

        return $this->success($notificaciones);
    }


    public function getData()
    {
        setlocale(LC_TIME, 'es');

        $modulos = Abconfig::with([
            'carreras' => function ($query) {
                $query->select('carreras.nombre', 'carreras.id', 'carreras.config_id');
            },
        ])->select('ab_config.id', 'ab_config.etapa')->get();

        foreach ($modulos as $modulo) {
            $modulo->modulo_selected = false;
            $modulo->carreras_selected = false;
        }

        $notificaciones = PushNotification::orderByDesc('created_at')->paginate(15);
        $_notificaciones = collect();

        foreach ($notificaciones as $notificacion) {

            $dest = collect(json_decode($notificacion->destinatarios));
            $dest = $dest->groupBy('modulo_nombre');
            $dest = $dest->toArray();
            $f_envio = new Carbon($notificacion->created_at);

            $_notificaciones->push([
                'id' => $notificacion->id,
                'titulo' => $notificacion->titulo,
                'texto' => $notificacion->texto,
                'success' => $notificacion->success,
                'failure' => $notificacion->failure,
                'created_at' => $f_envio->formatLocalized('%H:%M,  %d de %B de %Y'),
                'destinatarios' => $dest,
                "dialog" => false
            ]);
        }

        $paginate = array(
            'total_paginas' => $notificaciones->lastPage()
        );

        return response()->json(compact('modulos', '_notificaciones', 'paginate'), 200);
    }

    public function detalle(PushNotification $notificacion)
    {

        $estado = ['PENDIENTE', 'ENVIADO'];
        $segmentacion = json_decode($notificacion->destinatarios);
        $resumen_estado['alcanzados'] = $notificacion->success;
        $resumen_estado['no_alcanzados'] = $notificacion->failure;
        $detalles_json = collect(json_decode($notificacion->detalles_json));
        $aUsuarios = $detalles_json->pluck('usuarios');
        $resumen_estado['objetivo'] = 0;
        $lotes = collect();

        foreach ($detalles_json as $dj) {
            $q_users = count($dj->usuarios);
            $resumen_estado['objetivo'] += $q_users;
            $lotes->push([
                'datetime' => $dj->hora_envio,
                'quantity' => $q_users,
                'estado' => $estado[$dj->estado_envio]
            ]);
        }

        $resumen_estado['pendientes'] = $resumen_estado['objetivo'] - ($resumen_estado['alcanzados'] + $resumen_estado['no_alcanzados']);
        $resumen_estado['pendientes'] <= 0 && $resumen_estado['pendientes'] = 0;

        return response()->json(compact('segmentacion', 'resumen_estado', 'lotes'), 200);
    }
}
