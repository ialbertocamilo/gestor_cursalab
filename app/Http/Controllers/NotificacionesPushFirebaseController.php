<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificacionPush\NotificacionPushSearchResource;

use App\Models\Abconfig;
use App\Models\Eventos;
use App\Models\Usuario;
use App\Models\Matricula;
use App\Models\ActividadEvento;
use App\Models\NotificacionPush;

use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificacionesPushFirebaseController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('es');
    }

    public function index()
    {
        $data = array();
        return view('notificaciones_push.index', $data);
    }

    public function enviarNotificacionCustom(Request $request)
    {
        $destinatarios = json_decode($request->destinatarios);
        $_destinatarios = collect();
        foreach ($destinatarios as $mod) {
            foreach ($mod->carreras as $carr) {
                $usuarios_matriculas = Matricula::where('carrera_id', $carr->carrera_id)
                    ->where('presente', 1)
                    ->get(['usuario_id']);
                foreach ($usuarios_matriculas as $us) {
                    $_destinatarios->push($us->usuario_id);
                }
            }
        }
        //NOTIFICACION
        $push_chunk = 500;
        $envio_inicio = 2;
        $intervalo = 15;
        $_detalles_json = collect();
        $i = 1;
        $chunk = array_chunk($_destinatarios->all(), $push_chunk);
        $ahora = Carbon::now();
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

        $nueva_notificacion = new NotificacionPush();
        $nueva_notificacion->titulo = $request->titulo;
        $nueva_notificacion->texto = $request->texto;
        $nueva_notificacion->destinatarios = $request->destinatarios;
        $nueva_notificacion->creador_id = $request->creador_id;
        $nueva_notificacion->estado_envio= 1; // PENDIENTE (aún no iniciado)
        $nueva_notificacion->detalles_json = json_encode($_detalles_json);
        $nueva_notificacion->success= 0;
        $nueva_notificacion->failure= 0;
        $nueva_notificacion->save();
        return response()->json(['error' => false, 'title'=> 'Notificación registrada', 'msg' => "Se enviará durante los próximos minutos."],200);
    }
    public function enviarNotificacionFormularioEvento($evento_id)
    {
        $evento = Eventos::where('id', $evento_id)->first();
        $asistentes_evento = ActividadEvento::where('evento_id', $evento->id)
            ->where('estado', 1)
            ->pluck('usuario_id');
        $array_tokens = Usuario::whereIn('id', $asistentes_evento)->whereNotNull('token_firebase')->pluck('token_firebase');
        info($array_tokens);
        $data_notificacion = array(
            'tipo' => 'notificacion_formulario',
            'link' => $evento->link_google_form
        );
        $titulo = $evento->titulo;
        $body = 'Haz click aquí para ir al formulario.';
        try {
            $resultado = NotificacionPush::enviar($titulo, $body, $array_tokens, $data_notificacion);
            return response()->json([
                'error' => false,
                'msg' => 'Notificaciones enviadas correctamente.'
            ], 200);
        } catch (\Throwable $th) {
            info($th);
            return response()->json([
                'error' => true,
                'msg' => 'Error en el servidor, intente en un minuto.'
            ], 200);
        }
    }

    public function search(Request $request)
    {
        $notificaciones = NotificacionPush::search($request);

        NotificacionPushSearchResource::collection($notificaciones);

        return $this->success($notificaciones);
    }

    public function getListSelects()
    {
        $estados = [];

        $modulos = Abconfig::with([
            'carreras' => function ($query) {
                $query->select('carreras.nombre', 'carreras.id', 'carreras.config_id');
            },
        ])->select('ab_config.id', 'ab_config.etapa')->get();

        foreach ($modulos as $modulo) {
            $modulo->modulo_selected = false;
            $modulo->carreras_selected = false;
        }

        return $this->success(get_defined_vars());
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
        $notificaciones = NotificacionPush::orderByDesc('created_at')->paginate(15);
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

    public function detalle(NotificacionPush $notificacion){
        $estado = ['PENDIENTE','ENVIADO'];
        $segmentacion = json_decode($notificacion->destinatarios);
        $resumen_estado['alcanzados'] = $notificacion->success;
        $resumen_estado['no_alcanzados'] = $notificacion->failure;
        $detalles_json = collect(json_decode($notificacion->detalles_json));
        $aUsuarios = $detalles_json->pluck('usuarios');
        $resumen_estado['objetivo'] = 0;
        $lotes = collect();
        foreach($detalles_json as $dj){
            $q_users = count($dj->usuarios);
            $resumen_estado['objetivo'] +=$q_users ;
            $lotes->push([
                'datetime'=> $dj->hora_envio,
                'quantity' =>$q_users,
                'estado' =>  $estado[$dj->estado_envio]
            ]);
        }
        $resumen_estado['pendientes'] = $resumen_estado['objetivo'] - ($resumen_estado['alcanzados'] + $resumen_estado['no_alcanzados']);
        $resumen_estado['pendientes']<=0 && $resumen_estado['pendientes'] = 0;
        return response()->json(compact('segmentacion','resumen_estado','lotes'), 200);
    }
}
