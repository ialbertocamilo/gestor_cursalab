<?php

namespace App\Http\Controllers;

use Config;
use DateTime;

use App\Models\Grupo;
use App\Models\Eventos;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Matricula;
use App\Models\CuentaZoom;
use App\Models\Usuario_rest;
use App\Models\ActividadEvento;
use App\Models\AsistenteEvento;

use Carbon\Carbon;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\ZoomApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\EventosExport;
use App\Exports\AulasVirtualesExport;
use App\Exports\EventosAsistentesExport;

use GuzzleHttp\Exception\RequestException;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class EventosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt', ['except' => ['crearEventoEnVivo', 'cambiarEstadoEvento', 'getDetallesxEvento', 'getEventos', 'finalizar_evento_con_webhook',
            'cambiar_estado_ausente', 'cambiar_estado_activo', 'descargarEventosEnVivo'

            // TEMPORAL POR DESARROLLO
            // 'exportarEventosApp', 'exportarAsistentesEventosApp'
            // TEMPORAL POR DESARROLLO

        ]]);
        Carbon::setLocale('es');
        return auth()->shouldUse('api');
    }

    public function getEventos_x_usuario($usuario_id)
    {
        $eventos_zoom = Eventos::join('asistentes_evento AS asistencia', 'asistencia.evento_id', '=', 'eventos.id')
            ->join('cuentas_zoom as zoom', 'zoom.id', 'eventos.cuenta_zoom_id')
            ->where('asistencia.usuario_id', $usuario_id)
            ->where('tipo_evento_id', 1)
            ->where('estado_id', 2)
            ->orWhere(function ($query) use ($usuario_id) {
                $query->where('asistencia.usuario_id', $usuario_id);
                $query->where('tipo_evento_id', 1);
                $query->where('estado_id', 3);
            })
            ->select(
                'eventos.id as evento_id',
                'eventos.link_zoom',
                'eventos.link_google_form',
                'eventos.titulo',
                'eventos.descripcion',
                'eventos.duracion',
                'eventos.usuario_id',
                'eventos.fecha_inicio',
                'eventos.fecha_fin',
                'eventos.tipo_evento_id',
                'eventos.zoom_meeting_id',
                'eventos.zoom_password',
                'eventos.estado_id',
                'zoom.id as cuenta_zoom_id',
                'zoom.api_key',
                'zoom.client_secret',
                'zoom_userid',
                'zoom.pmi',
                'zoom.sdk_token',
                'zak_token',
                'refresh_token'
            )
            ->get();

        $eventos_vimeo = Eventos::getVimeoEventsByUser($usuario_id);

        $_eventos = collect();
        foreach ($eventos_zoom as $e_zoom) {
            $_eventos->push($e_zoom);
        }
        foreach ($eventos_vimeo as $e_vimeo) {
            $_eventos->push($e_vimeo);
        }
        $ordenado = $_eventos->sortBy('fecha_inicio');

        return response()->json(['eventos' => $ordenado->values()->all()], 200);
    }

    public function validarHorario(Request $request)
    {
        $cuentas_zoom = CuentaZoom::where('estado', 1)
            // ->where('tipo', 'PRO')
            ->inRandomOrder()->get();
        $fecha = $request->fecha;

        $hora_inicio = new Carbon(date('Y-m-d', strtotime($request->fecha)) . ' ' . $request->hr_inicio);

        $hr_inicio = new Carbon(date('Y-m-d', strtotime($request->fecha)) . ' ' . $request->hr_inicio);

        $hr_verificar = new Carbon(date('Y-m-d', strtotime($request->fecha)) . ' ' . $request->hr_inicio);

        // $horas = intval($request->duracion / 60);
        // $minutos = $request->duracion % 60;
        // $hr_inicio->addHours($horas);
        $hr_inicio->addMinutes($request->duracion);
        $hr_fin = $hr_inicio->format('H:i:s');
        // $encontro = false;
        $zoom = null;
        $hr_verificar->subMinutes(30);
        // return date('Y-m-d', styrtotime($fecha)) . ' ' . $hr_fin;
        // return  date('Y-m-d', strtotime($fecha)) . ' ' . $hora_inicio->format('H:i:s');
        $data = $this->crear_evento_desde_validarHorario($fecha, $cuentas_zoom, $hr_fin, $hr_verificar, $request, $hora_inicio);
        if ($data) {
            return response()->json(['zoom_data' => $data['zoom'], 'evento_resservado_data' => $data['evento_resservado_data'], 'token' => $data['token'], 'success' => true], 200);
        } else {
            $hr_verificar->addMinutes(30);
            $data = $this->crear_evento_desde_validarHorario($fecha, $cuentas_zoom, $hr_fin, $hr_verificar, $request, $hora_inicio);
            if ($data) {
                return response()->json(['zoom_data' => $data['zoom'], 'evento_resservado_data' => $data['evento_resservado_data'], 'token' => $data['token'], 'success' => true], 200);
            }
            return response()->json(['msg' => 'El horario seleccionado esta ocupado.', 'success' => false], 200);
        }
    }

    private function crear_evento_desde_validarHorario($fecha, $cuentas_zoom, $hr_fin, $hr_verificar, $request, $hora_inicio)
    {
        $encontro = false;
        foreach ($cuentas_zoom as $c_zoom) {
            $id = $c_zoom['id'];

            $existe = Eventos::where('cuenta_zoom_id', '=', $id)
                ->where('fecha_inicio', '<=', date('Y-m-d', strtotime($fecha)) . ' ' . $hr_fin)
                ->where('fecha_fin', '>=', date('Y-m-d', strtotime($fecha)) . ' ' . $hr_verificar->format('H:i:s'))
                ->whereNotIn('estado_id', [5])
                ->first(['id']);
            if (is_null($existe)) {
                $encontro = true;
                $zoom = $c_zoom;
                $nuevo_evento = new Eventos();
                $nuevo_evento->tipo_evento_id = 1; // REUNION ZOOM
                $nuevo_evento->estado_id = 1; // PRE RESERVA
                $nuevo_evento->usuario_id = $request->usuario_id;
                $nuevo_evento->titulo = "";
                $nuevo_evento->link_google_form = "";
                $nuevo_evento->descripcion = "";
                $nuevo_evento->fecha_inicio = date('Y-m-d', strtotime($fecha)) . ' ' . $hora_inicio->format('H:i:s');
                $nuevo_evento->fecha_fin = date('Y-m-d', strtotime($fecha)) . ' ' . $hr_fin;
                $nuevo_evento->duracion = $request->duracion;
                $nuevo_evento->cuenta_zoom_id = $id;
                $nuevo_evento->zoom_meeting_id = null;
                $nuevo_evento->zoom_password = null;

                $save = $nuevo_evento->save();
                $zoomApi = new ZoomApi();
                $access_token = $zoomApi->generateJWTKey($id);
                // \Log::info('no existe eventos');
                break;
            } else {
                $encontro = false;
                $zoom = null;
                // \Log::info('existen eventos   ' .$existe);
                continue;
            }
        }
        if ($encontro) {
            return ['zoom' => $zoom, 'evento_resservado_data' => $nuevo_evento, 'token' => $access_token];
        }
        return false;
    }

    public function validarAsistentesxCuenta($evento_id, $numero_asistentes)
    {
        /**
         * VALIDAR ASISTENTES <= 100
         */
        $evento = Eventos::with([
            'cuentaZoom' => function ($query) {
                $query->select('id', 'tipo');
            }
        ])
            ->where('id', $evento_id)
            ->select('id', 'cuenta_zoom_id', 'fecha_inicio', 'fecha_fin')
            ->first();
        if ($numero_asistentes <= 100) {
            // USAR POOL DE CUENTAS PRO
            if ($evento->cuentaZoom->tipo != 'PRO') {
                $cambiar = $this->cambiarCuentaZoomID_a_evento($evento, 'PRO');
                if (!$cambiar)
                    return false;
            }
            return true;
            /**
             * VALIDAR ASISTENTES <= 500
             */
        } else if ($numero_asistentes <= 400) {
            // USAR POOL DE CUENTAS BUSINESS
            if ($evento->cuentaZoom->tipo != 'BUSINESS') {
                $cambiar = $this->cambiarCuentaZoomID_a_evento($evento, 'BUSINESS');
                if (!$cambiar)
                    return false;
            }
            return true;
            /**
             * ASISTENTES MAYORES A 500
             */
        } else {
            return false;
        }
    }

    public function cambiarCuentaZoomID_a_evento(Eventos $evento, $tipo)
    {
        // Buscar cuentas zoom del tipo => $tipo disponibles para el horario del evento a editar
        $nueva_cuenta_zoom = $this->buscarCuentaZoomDisponiblexTipo($evento->fecha_inicio, $evento->fecha_fin, $tipo);
        if (is_null($nueva_cuenta_zoom))
            return false;
        $evento->cuenta_zoom_id = $nueva_cuenta_zoom;
        $evento->save();
        return true;
    }

    public function buscarCuentaZoomDisponiblexTipo($fecha_inicio, $fecha_fin, $tipo)
    {
        $cuentas_zoom = CuentaZoom::select('id', 'tipo', 'estado')
            ->where('estado', 1)->where('tipo', $tipo)
            ->inRandomOrder()->get();

        \Log::channel('eventos_virtuales')->info($cuentas_zoom);

        $zoom = null;
        foreach ($cuentas_zoom as $c_zoom) {
            $id = $c_zoom['id'];

            \Log::channel('eventos_virtuales')->info($fecha_fin . ' => ' . date('Y-m-d H:i:s', strtotime($fecha_fin)));
            \Log::channel('eventos_virtuales')->info($fecha_inicio . ' => ' . date('Y-m-d H:i:s', strtotime($fecha_inicio)));

            $existe = Eventos::where('cuenta_zoom_id', '=', $id)
                ->whereNotIn('estado_id', [4, 5])
                ->where('fecha_inicio', '<=', date('Y-m-d H:i:s', strtotime($fecha_fin)))
                ->where('fecha_fin', '>=', date('Y-m-d H:i:s', strtotime($fecha_inicio)))
                ->first(['id']);

            $existe2 = Eventos::where('cuenta_zoom_id', '=', $id)
                ->whereNotIn('estado_id', [4, 5])
                ->where('fecha_inicio', '<=', $fecha_fin)
                ->where('fecha_fin', '>=', $fecha_inicio)
                ->first(['id']);

            \Log::channel('eventos_virtuales')->info($existe ? 'existe' : 'no existe');
            \Log::channel('eventos_virtuales')->info($existe2 ? 'existe2' : 'no existe 2');

            if (!$existe) {
                $zoom = $id;
                break;
            }
        }
        return $zoom;
    }


    public function crearEvento(Request $request)
    {
        // return $request->all();
        $asistentes = json_decode($request->asistentes, true);
        if (count($asistentes) == 0) return response()->json(['error' => true, 'msg' => 'No ha seleccionado ningún asistente.'], 200);

        /**
         * Validamos si la cuenta zoom con la que se hizo la pre-reserva va de acuerdo
         * a la cantidad de asistentes agregados
         */
        // $validarAsistentesxCuenta = $this->validarAsistentesxCuenta($request->evento_id, count($asistentes));

        // if (!$validarAsistentesxCuenta) {
        //     return response()->json([
        //         'error' => true,
        //         'msg' => 'Ocurrió un error.' // FALTA
        //     ], 200);
        // }


        // DATOS DE EVENTO
        $evento = Eventos::find($request->evento_id);
        // OBTENEMOS EL REFRESH TOKEN
        $zoom = CuentaZoom::find($evento->cuenta_zoom_id);
        $fecha_inicio_evento1 = new Carbon($evento->fecha_inicio);
        $fecha_inicio_evento2 = new Carbon($evento->fecha_inicio);
        $fecha_inicio_evento2->addMinutes($evento->duracion);
        $fecha_fin_evento = $fecha_inicio_evento2->format('Y-m-d H:i:s');

        $client = new Client();
        $data = [
            "duration" => $evento->duracion,
            "settings" => [
                "host_video" => "true",
                "participant_video" => "true"
            ],
            "start_time" => date('Y-m-d\TH:i:s', strtotime($evento->fecha_inicio)),
            "topic" => $request->titulo,
            "agenda" => $request->descripcion,
            "timezone" => "America/Lima",
            "type" => 2,
            "password" => rand(111111, 999999)
        ];

        try {
            $response = $client->post('https://api.zoom.us/v2/users/' . $zoom->correo . '/meetings', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $zoom->refresh_token
                    // 'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJxdlZBUE1FSVFRaWFjZXhPRHdwM3V3IiwiZXhwIjoxNjAyMTcyMTY5fQ.0_UF4LUqk18pyi9VDZJ8gDVtDAbW9vnyooWWYZLprAc'
                ],
                'body' => json_encode($data)
            ]);
            $body = json_decode($response->getBody());
            \Log::channel('eventos_virtuales')->info(print_r($body, true));


        } catch (RequestException $e) {
            $msg = json_decode($e->getResponse()->getBody());
            \Log::channel('eventos_virtuales')->info("---------------------- CATCH CREAR EVENTO ----------------------");
            \Log::channel('eventos_virtuales')->info(print_r($msg, true));
            \Log::channel('eventos_virtuales')->info("CATCH AL CREAR EVENTO => $evento->i :: STATUS CODE =>" . (isset($msg->code) && $msg->code) ? $msg->code : 'sin mensaje de codigo'
            );

            switch ($msg->code) {
                case 124:
                    $zoomApi = new ZoomApi();
                    $access_token = $zoomApi->generateJWTKey($evento->cuenta_zoom_id);
                    $zoom = CuentaZoom::find($evento->cuenta_zoom_id);
                    \Log::channel('eventos_virtuales')->info("SEGUNDO INTENTO POR TOKEN INVALIDO");

                    $response = $client->post('https://api.zoom.us/v2/users/' . $zoom->correo . '/meetings', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $zoom->refresh_token
                        ],
                        'body' => json_encode($data)
                    ]);

                    $body = json_decode($response->getBody());
                    \Log::channel('eventos_virtuales')->info(print_r($body, true));


                    $evento->tipo_evento_id = 1; // REUNION ZOOM
                    $evento->estado_id = 2;
                    $evento->usuario_id = $request->usuario_id;
                    $evento->titulo = $request->titulo;
                    $evento->descripcion = $request->descripcion;
                    // $evento->cuenta_zoom_id = $request->cuenta_zoom_id;
                    $evento->link_zoom = $body->join_url;
                    $evento->link_google_form = $request->link_google_form;
                    $evento->zoom_meeting_id = $body->id;
                    $evento->zoom_password = $body->password;
                    $save = $evento->save();

                    // REGISTRAR AL CREADOR DEL EVENTO COMO ASISTENTE
                    $creador_asistencia = new AsistenteEvento();
                    $creador_asistencia->usuario_id = $request->usuario_id;
                    $creador_asistencia->evento_id = $evento->id;
                    $creador_asistencia->primera_asistencia = null;
                    $creador_asistencia->segunda_asistencia = null;
                    $creador_asistencia->save();

                    // ACTIVIDAD CREADOR
                    $creador_actividad = new ActividadEvento();
                    $creador_actividad->estado = 0; // 0 ausente 1 activo
                    $creador_actividad->cant_ingresos = 0;
                    $creador_actividad->cant_salidas = 0;
                    $creador_actividad->ultimo_ingreso = null;
                    $creador_actividad->ultima_salida = null;
                    $creador_actividad->usuario_id = $request->usuario_id;
                    $creador_actividad->evento_id = $evento->id;
                    $creador_actividad->save();

                    $_tokensFirebase = collect();
                    // OBTENER USUARIOS OCUPADOS
                    $usuarios_ocupados = Eventos::join('asistentes_evento as asistencia', 'asistencia.evento_id', 'eventos.id')
                        ->join('usuarios', 'usuarios.id', 'asistencia.usuario_id')
                        // NO CONSIDERAR LOS EVENTOS CANCELADOS NI FINALIZADOS
                        ->whereNotIn('eventos.estado_id', [4, 5])
                        // NO CONSIDERAR LOS EVENTOS CANCELADOS NI FINALIZADOS
                        ->where('eventos.fecha_inicio', '<=', $fecha_fin_evento)
                        ->where('eventos.fecha_fin', '>=', $fecha_inicio_evento1)
                        ->pluck('usuarios.dni');
                    $_asistentes = collect();
                    foreach ($asistentes as $as) {
                        $_asistentes->push($as['dni']);
                    }
                    // RESTAR DNI DE LOS USUARIOS OCUPADOS A LOS QUE LLEGAN DE LA APP ($_ASISTENTES)
                    $asistentes_finales = $_asistentes->diff($usuarios_ocupados);

                    // ASISTENTES QUE NO SE AGREGARON
                    $usuarios_no_agregados = $_asistentes->intersect($usuarios_ocupados);
                    // ASISTENTES QUE NO SE AGREGARON

                    // REGISTRAR INVITADOS AL EVENTO
                    foreach ($asistentes_finales->all() as $key => $value) {
                        $usuario = Usuario_rest::where('dni', $value)
                            ->first(['token_firebase', 'id']);
                        if ($usuario) {
                            $_tokensFirebase->push($usuario->token_firebase);
                            $asistencia = new AsistenteEvento();
                            $asistencia->usuario_id = $usuario->id;
                            $asistencia->evento_id = $evento->id;
                            $asistencia->primera_asistencia = null;
                            $asistencia->segunda_asistencia = null;
                            $asistencia->save();
                        }
                    }

                    // EVNIAR NOTIFICACIONES PUSH CON FIREBASE
                    if (count($_tokensFirebase) > 0) {
                        $firebase = new FirebaseController();
                        \Log::channel('eventos_virtuales')->info('Array de tokens firebase de los asistentes');
                        \Log::channel('eventos_virtuales')->info($_tokensFirebase);
                        \Log::channel('eventos_virtuales')->info('-----------------------------------------------------------------------');
                        // ENVIAR NOTIFICACIONES AL ARRAY DE TOKENS
                        $fecha = date('d/m/Y', strtotime($evento->fecha_inicio));
                        $horario = date('H:i', strtotime($evento->fecha_inicio));

                        $title = '¡Hola! tienes un evento nuevo agendado';
                        $body = "Se llevará a cabo el $fecha a las $horario horas. Revisa la lista de eventos en la app.";
                        $notificaciones_enviadas = $firebase->enviarNotificacionesGrupo($title, $body, $_tokensFirebase);
                        \Log::channel('eventos_virtuales')->info('ConfirmaciÃ³n de firebase');
                        \Log::channel('eventos_virtuales')->info($notificaciones_enviadas);
                        \Log::channel('eventos_virtuales')->info('-----------------------------------------------------------------------');
                    }

                    return response()->json([
                        'error' => false,
                        'msg' => 'Evento creado correctamente.',
                        'zoom_data' => $body,
                        'evento_data' => $evento,
                        'usuarios_no_agregados' => $this->parseUsuariosNoAgregados($usuarios_no_agregados)
                    ], 200);
                    break;
                case 429:
                    \Log::channel('eventos_virtuales')->info("Cuenta zoom => $evento->cuenta_zoom_id, ha sobrepasado el limite de 100 request diarios :: Mensaje => " . (isset($msg->message) && $msg->message) ? $msg->message : 'sin mensaje'
                    );
                    return response()->json([
                        'error' => true,
                        'msg' => 'Ocurrió un error. Vuelva a intentarlo en unos minutos. Code 429.', // FALTA
                        'zoom_data' => null,
                        'evento_data' => null
                    ], 200);
                    break;
                case 300:
                    \Log::channel('eventos_virtuales')->info(""
                    . "Cuenta zoom => $evento->cuenta_zoom_id, ha sobrepasado el limite de 100 request diarios :: "
                    . " Mensaje => " . (isset($msg->message) && $msg->message) ? $msg->message : 'sin mensaje'
                    );
                    return response()->json([
                        'error' => true,
                        'msg' => 'Ocurrió un error. Vuelva a intentarlo en unos minutos. Code 300.', // FALTA
                        'zoom_data' => null,
                        'evento_data' => null
                    ], 200);
                    break;
            }
            return;
        }
        $evento->tipo_evento_id = 1; // REUNION ZOOM
        $evento->estado_id = 2;
        $evento->usuario_id = $request->usuario_id;
        $evento->titulo = $request->titulo;
        $evento->descripcion = $request->descripcion;
        // $evento->cuenta_zoom_id = $request->cuenta_zoom_id;
        $evento->link_zoom = $body->join_url;
        $evento->link_google_form = $request->link_google_form;
        $evento->zoom_meeting_id = $body->id;
        $evento->zoom_password = $body->password;
        $save = $evento->save();

        // REGISTRAR AL CREADOR DEL EVENTO COMO ASISTENTE
        $creador_asistencia = new AsistenteEvento();
        $creador_asistencia->usuario_id = $request->usuario_id;
        $creador_asistencia->evento_id = $evento->id;
        $creador_asistencia->primera_asistencia = null;
        $creador_asistencia->segunda_asistencia = null;
        $creador_asistencia->save();

        // ACTIVIDAD CREADOR
        $creador_actividad = new ActividadEvento();
        $creador_actividad->estado = 0; // 0 ausente 1 activo
        $creador_actividad->cant_ingresos = 0;
        $creador_actividad->cant_salidas = 0;
        $creador_actividad->ultimo_ingreso = null;
        $creador_actividad->ultima_salida = null;
        $creador_actividad->usuario_id = $request->usuario_id;
        $creador_actividad->evento_id = $evento->id;
        $creador_actividad->save();

        $_tokensFirebase = collect();
        // OBTENER USUARIOS OCUPADOS
        $usuarios_ocupados = Eventos::join('asistentes_evento as asistencia', 'asistencia.evento_id', 'eventos.id')
            ->join('usuarios', 'usuarios.id', 'asistencia.usuario_id')
            // NO CONSIDERAR LOS EVENTOS CANCELADOS NI FINALIZADOS
            ->whereNotIn('eventos.estado_id', [4, 5])
            // NO CONSIDERAR LOS EVENTOS CANCELADOS NI FINALIZADOS
            ->where('eventos.fecha_inicio', '<=', $fecha_fin_evento)
            ->where('eventos.fecha_fin', '>=', $fecha_inicio_evento1)
            ->pluck('usuarios.dni');
        $_asistentes = collect();
        foreach ($asistentes as $as) {
            $_asistentes->push($as['dni']);
        }
        // RESTAR DNI DE LOS USUARIOS OCUPADOS A LOS QUE LLEGAN DE LA APP ($_ASISTENTES)
        $asistentes_finales = $_asistentes->diff($usuarios_ocupados);

        // ASISTENTES QUE NO SE AGREGARON
        $usuarios_no_agregados = $_asistentes->intersect($usuarios_ocupados);
        // ASISTENTES QUE NO SE AGREGARON

        // REGISTRAR INVITADOS AL EVENTO
        foreach ($asistentes_finales->all() as $key => $value) {
            $usuario = Usuario_rest::where('dni', $value)
                ->first(['token_firebase', 'id']);
            if ($usuario) {
                $_tokensFirebase->push($usuario->token_firebase);
                $asistencia = new AsistenteEvento();
                $asistencia->usuario_id = $usuario->id;
                $asistencia->evento_id = $evento->id;
                $asistencia->primera_asistencia = null;
                $asistencia->segunda_asistencia = null;
                $asistencia->save();
            }
        }
        // EVNIAR NOTIFICACIONES PUSH CON FIREBASE
        if (count($_tokensFirebase) > 0) {
            $firebase = new FirebaseController();
            \Log::channel('eventos_virtuales')->info('Array de tokens firebase de los asistentes');
            \Log::channel('eventos_virtuales')->info($_tokensFirebase);
            \Log::channel('eventos_virtuales')->info('-----------------------------------------------------------------------');
            // ENVIAR NOTIFICACIONES AL ARRAY DE TOKENS
            $fecha = date('d/m/Y', strtotime($evento->fecha_inicio));
            $horario = date('H:i', strtotime($evento->fecha_inicio));

            $title = '¡Hola! tienes un evento nuevo agendado';
            $body = "Se llevará a cabo el $fecha a las $horario horas. Revisa la lista de eventos en la app.";
            $notificaciones_enviadas = $firebase->enviarNotificacionesGrupo($title, $body, $_tokensFirebase);
            \Log::channel('eventos_virtuales')->info('ConfirmaciÃ³n de firebase');
            \Log::channel('eventos_virtuales')->info($notificaciones_enviadas);
            \Log::channel('eventos_virtuales')->info('-----------------------------------------------------------------------');
        }

        return response()->json([
            'error' => false,
            'msg' => 'Evento creado correctamente.',
            'zoom_data' => $body,
            'evento_data' => $evento,
            'usuarios_no_agregados' => $this->parseUsuariosNoAgregados($usuarios_no_agregados)
        ], 200);
    }

    public function getAsistentensxEvento(Request $request)
    {
        $evento = Eventos::find($request->evento_id, ['usuario_id']);
        $creador_id = $evento->usuario_id;
        // RETORNAR TODOS LOS ASISTENTES MENOS EL CREADOR DEL EVENTO
        $asistentes = AsistenteEvento::join('usuarios', 'usuarios.id', 'asistentes_evento.usuario_id')
            ->where('evento_id', $request->evento_id)
            ->whereNotIn('usuarios.id', [$creador_id])
            ->get(['usuarios.dni', 'usuarios.nombre', 'usuarios.botica']);

        return response()->json($asistentes, 200);
    }

    public function validarHorarioEditarEvento(Request $request)
    {
        // return $request->all();
        /**
         * VALIDAR SI EL NUEVO HORARIO SELECCIONADO ESTA DISPONIBLE
         */
        $evento_id = $request->evento_id;
        $fecha_evento = $request->fecha_evento;
        // HORA INICIO
        $hora_inicio = new Carbon(date('Y-m-d', strtotime($fecha_evento)) . ' ' . $request->hora_inicio);
        // CALCULO DE HORA FIN A PARTIR DE LA HORA INICIO
        $hr_inicio = new Carbon(date('Y-m-d', strtotime($fecha_evento)) . ' ' . $request->hora_inicio);
        $hr_inicio->addMinutes($request->duracion);
        $hr_fin = $hr_inicio->format('H:i:s');

        $zoom = CuentaZoom::find($request->cuenta_zoom_id);
        $existe = Eventos::where('cuenta_zoom_id', '=', $zoom->id)
            ->whereNotIn('eventos.id', [$evento_id])
            ->where('fecha_inicio', '<=', date('Y-m-d', strtotime($fecha_evento)) . ' ' . $hr_fin)
            ->where('fecha_fin', '>=', date('Y-m-d', strtotime($fecha_evento)) . ' ' . $hora_inicio->format('H:i:s'))
            ->first(['id']);
        if (is_null($existe)) {
            /**
             * CAMBIAMOS Y "RESERVAMOS" EL NUEVO HORARIO AL EVENTO "
             */
            $evento = Eventos::find($request->evento_id);
            $creador_id = $evento->usuario_id;
            $cuenta_zoom = CuentaZoom::find($evento->cuenta_zoom_id);
            $client = new Client();
            $data = [
                "start_time" => date('Y-m-d\TH:i:s', strtotime($fecha_evento . ' ' . $hora_inicio->format('H:i:s'))),
                "duration" => $request->duracion,
                "settings" => [
                    "host_video" => "true",
                    "participant_video" => "true"
                ],
                "topic" => $evento->titulo,
                "agenda" => $evento->descripcion,
                "timezone" => "America/Lima",
                "type" => 2,
            ];
            try {
                $response = $client->patch('https://api.zoom.us/v2/meetings/' . $evento->zoom_meeting_id, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $cuenta_zoom->refresh_token
                        // 'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJxdlZBUE1FSVFRaWFjZXhPRHdwM3V3IiwiZXhwIjoxNjAyMTcyMTY5fQ.0_UF4LUqk18pyi9VDZJ8gDVtDAbW9vnyooWWYZLprAc'
                    ],
                    'body' => json_encode($data)
                ]);
                $body = json_decode($response->getBody());
                \Log::channel('eventos_virtuales')->info("respuesta de zoom $body");
            } catch (RequestException $e) {
                \Log::channel('eventos_virtuales')->info("catch de zoom" . $e->getResponse()->getBody());
                $zoomApi = new ZoomApi();
                $access_token = $zoomApi->generateJWTKey($cuenta_zoom->id);
                $this->validarHorarioEditarEvento($request);
            }

            $evento = Eventos::find($evento_id);
            $evento->fecha_inicio = date('Y-m-d', strtotime($fecha_evento)) . ' ' . $hora_inicio->format('H:i:s');
            $evento->fecha_fin = date('Y-m-d', strtotime($fecha_evento)) . ' ' . $hr_fin;
            $evento->duracion = $request->duracion;
            $evento->save();

            return response()->json(['msg' => 'Horario actualizado.', 'disponible' => true], 200);
        } else {
            return response()->json(['msg' => 'Horario no disponible.', 'disponible' => false], 200);
        }
    }

    public function editarEvento(Request $request)
    {
        $evento = Eventos::find($request->evento_id);
        $fecha_inicio_evento1 = new Carbon($evento->fecha_inicio);
        $fecha_inicio_evento2 = new Carbon($evento->fecha_inicio);
        $fecha_inicio_evento2->addMinutes($evento->duracion);
        $fecha_fin_evento = $fecha_inicio_evento2->format('Y-m-d H:i:s');

        $creador_id = $evento->usuario_id;
        if ($request->cambio_datos) {
            /**
             * ACTUALIZAR EVENTO EN LA BD
             */
            $evento->duracion = $request->duracion;
            $evento->titulo = $request->titulo;
            $evento->descripcion = $request->descripcion;
            $evento->link_google_form = $request->link_google_form;
            $evento->save();
        }
        if ($request->cambio_asistentes) {
            /**
             * EDITAR PARTICIPANTES
             */
            $_tokensFirebase = collect();
            // ASISTENTES APP
            $asistentes_app = collect();
            foreach ($request->asistentes as $asistente) {
                $as = Usuario::where('dni', $asistente['dni'])->first(['id']);
                $asistentes_app->push($as->id);
            }
            // ASISTENTES BD SIN CONSIDERAR AL CREADOR DEL EVENTO
            $asistentes_BD = AsistenteEvento::where('evento_id', $request->evento_id)
                ->whereNotIn('usuario_id', [$creador_id])
                ->pluck('usuario_id');
            // ASISTENTES NUEVOS
            $asistentes_agregar = $asistentes_app->diff($asistentes_BD);
            // OBTENER USUARIOS OCUPADOS
            $usuarios_ocupados = Eventos::join('asistentes_evento as asistencia', 'asistencia.evento_id', 'eventos.id')
                ->join('usuarios', 'usuarios.id', 'asistencia.usuario_id')
                // NO CONSIDERAR LOS EVENTOS CANCELADOS NI FINALIZADOS
                ->whereNotIn('eventos.estado_id', [4, 5])
                // NO CONSIDERAR LOS EVENTOS CANCELADOS NI FINALIZADOS
                ->where('eventos.fecha_inicio', '<=', $fecha_fin_evento)
                ->where('eventos.fecha_fin', '>=', $fecha_inicio_evento1)
                ->pluck('usuarios.id');
            // RESTAR A LOS USUARIOS QUE SE VAN A AGREGAR LOS USUARIOS OCUPADOS
            $asistentes_agregar_all = collect($asistentes_agregar->all());
            $asistentes_agregar_final = $asistentes_agregar_all->diff($usuarios_ocupados);

            foreach ($asistentes_agregar_final->all() as $key => $value) {
                $asistencia = new AsistenteEvento();
                $asistencia->usuario_id = $value;
                $asistencia->evento_id = $evento->id;
                $asistencia->primera_asistencia = null;
                $asistencia->segunda_asistencia = null;
                $asistencia->save();
                $usuario = Usuario::where('id', $value)
                    ->first(['token_firebase', 'id']);
                // if (count($usuario)) {
                // if (!$usuario->token_firebase) {
                $_tokensFirebase->push($usuario->token_firebase);
                // \Log::channel('eventos_virtuales')->info('evento_id : '.$evento->id.' ==== token asistente: ' . $usuario->token_firebase);
                // }
                // }
            }
            // ASISTENTES QUE SE HAN ELIMINADO
            $asistentes_eliminar = $asistentes_BD->diff($asistentes_app);
            foreach ($asistentes_eliminar->all() as $key => $value) {
                AsistenteEvento::where('usuario_id', $value)->where('evento_id', $evento->id)->delete();
                ActividadEvento::where('usuario_id', $value)->where('evento_id', $evento->id)->delete();
            }
            // NOTIFICAR A LOS NUEVOS USUARIOS QUE HAN SIDO AGREGADOS AL EVENTO
            if (count($_tokensFirebase) > 0) {
                $firebase = new FirebaseController();
                $fecha = date('d/m/Y', strtotime($evento->fecha_inicio));
                $horario = date('H:i', strtotime($evento->fecha_inicio));
                $title = '¡Hola! tienes un evento nuevo agendado';
                $body = "Se llevará a cabo el  $fecha a las  $horario horas. Revisa la lista de eventos en la app.";

                $notificaciones_enviadas = $firebase->enviarNotificacionesGrupo($title, $body, $_tokensFirebase);
                \Log::channel('eventos_virtuales')->info("TOKENS ENVIADOS ==> $_tokensFirebase, EVENTO ID-NOMBRE => $evento->id - $evento->titulo ");
                \Log::channel('eventos_virtuales')->info("RESPUESTA FIREBASE ==> $notificaciones_enviadas");
            }
            // NOTIFICAR A LOS ASISTENTES QUE NO SE ELIMINARON SI ES QUE HUBO CAMBIO DE FECHA
            if ($request->cambio_fecha) {
                $asistentes_no_eliminados = $asistentes_BD->diff($asistentes_eliminar);
                $_tokens_asistentes_no_eliminados = collect();
                foreach ($asistentes_no_eliminados as $key => $value) {
                    $usuario = Usuario::where('id', $value)
                        ->first(['token_firebase', 'id']);
                    // if (count($usuario)) {
                    // if (!$usuario->token_firebase) {
                    $_tokensFirebase->push($usuario->token_firebase);
                    // \Log::channel('eventos_virtuales')->info('evento_id : '.$evento->id.'token asistente: ' . $usuario->token_firebase);
                    // }
                    // }
                }
                if (count($_tokens_asistentes_no_eliminados) > 0) {
                    $firebase = new FirebaseController();
                    $fecha = date('d/m/Y', strtotime($evento->fecha_inicio));
                    $horario = date('H:i', strtotime($evento->fecha_inicio));
                    $title = "Se ha actualizado la fecha de la reunión: $evento->titulo";
                    $body = "Se llevará a cabo el $fecha a las $horario horas. Revisa la lista de eventos en la app.";
                    $notificaciones_enviadas = $firebase->enviarNotificacionesGrupo($title, $body, $_tokens_asistentes_no_eliminados);
                    \Log::channel('eventos_virtuales')->info("TOKENS ENVIADOS ==> $_tokens_asistentes_no_eliminados, EVENTO ID-NOMBRE => $evento->id - $evento->titulo ");
                    \Log::channel('eventos_virtuales')->info("RESPUESTA FIREBASE ==> $notificaciones_enviadas");
                }
            }
        }
        return response()->json(['msg' => 'El evento se ha actualizado correctamente'], 200);
    }

    public function marcar_asistencia(Request $request)
    {
        $asistentes = ActividadEvento::where('evento_id', $request->evento_id)->where('estado', 1)->select('usuario_id')->get();
        $ahora = Carbon::now()->toDateTimeString();
        if (count($asistentes)) {

            foreach ($asistentes as $as) {
                $update = AsistenteEvento::where('usuario_id', $as['usuario_id'])->where('evento_id', $request->evento_id)->first();
                if ($request->num_asistencia == 1) { //PRIMERA ASISTENCIA
                    $msg = 'Primera';
                    $update->primera_asistencia = $ahora;
                } else if ($request->num_asistencia == 2) { // SEGUNDA ASISTENCIA
                    $msg = 'Segunda';
                    $update->segunda_asistencia = $ahora;
                }
                $update->save();
            }
            return response()->json(['msg' => $msg . ' asistencia marcada.'], 200);
        } else {
            return response()->json(['msg' => ' No se registraron asistencias, porque no existen usuarios presentes.'], 200);
        }
    }

    public function registrarActividad(Request $request)
    {
        $ahora = Carbon::now()->toDateTimeString();

        switch ($request->tipo) {
            case 1: // INGRESO
                $evento = Eventos::find($request->evento_id);

                // if ($evento->tipo_evento_id == 1) {
                //     $asistencia = AsistenteEvento::where('usuario_id', $request->usuario_id)
                //                 ->where('evento_id', $request->evento_id)
                //                 ->first();
                //     $asistencia->fecha_ingreso = $ahora;
                //     $asistencia->save();
                // }

                if ($evento->tipo_evento_id == 2) {
                    $actividad_evento = ActividadEvento::whereRaw(
                        'usuario_id = ? and evento_id = ?',
                        [$request->usuario_id, $request->evento_id]
                    )
                        ->first();

                    if (is_null($actividad_evento)) {
                        $actividad_evento = new ActividadEvento();
                        $actividad_evento->estado = 0; // 0 ausente 1 activo
                        $actividad_evento->cant_ingresos = 0;
                        $actividad_evento->cant_salidas = 0;
                        $actividad_evento->ultimo_ingreso = null;
                        $actividad_evento->ultima_salida = null;
                        $actividad_evento->usuario_id = $request->usuario_id;
                        $actividad_evento->evento_id = $request->evento_id;
                        $actividad_evento->save();
                    }
                }

                $actividad_evento = ActividadEvento::whereRaw(
                    'usuario_id = ? and evento_id = ?',
                    [$request->usuario_id, $request->evento_id]
                )
                    ->first();
                $actividad_evento->estado = 1; // 0 ausente 1 activo
                $actividad_evento->cant_ingresos = $actividad_evento->cant_ingresos + 1;
                $actividad_evento->ultimo_ingreso = $ahora;
                $actividad_evento->save();

                return response()->json(['msg' => 'Ok'], 200);
                break;
            case 0: // SALIDA DEL EVENTO
                $actividad_evento = ActividadEvento::whereRaw(
                    'usuario_id = ? and evento_id = ?',
                    [$request->usuario_id, $request->evento_id]
                )
                    ->first();
                $actividad_evento->estado = 0; // 0 ausente 1 activo
                $actividad_evento->cant_salidas = $actividad_evento->cant_salidas + 1;
                $actividad_evento->ultima_salida = $ahora;
                $actividad_evento->save();

                return response()->json(['msg' => 'Ok'], 200);
                break;
        }
    }

    public function busquedaUsuarios(Request $request)
    {
        $fecha_inicio = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;
        $creador_id = $request->creador_id;

        $usuarios = Eventos::getUsuariosNoDisponibles($request);

        $_usuarios = collect();
        foreach ($usuarios as $u) {
            $_usuarios->push($u['id']);
        }
        // EXCLUIR AL CREADOR DEL EVENTO
        $_usuarios->push($creador_id);
        $filtros = collect();
        $string = "";

        if ($request->filtro_botica == "1") {
            $filtros->push("%$request->filtro%");
            $string .= 'botica like ? ';
        }
        if ($request->filtro_nombre == "1") {
            $filtros->push("%$request->filtro%");
            $string .= (count($filtros) > 1) ? ' or nombre like ? ' : ' nombre like ? ';
        }
        if ($request->filtro_dni == "1") {
            $filtros->push("%$request->filtro%");
            $string .= (count($filtros) > 1) ? ' or dni like ? ' : ' dni like ? ';
        }
        $query = Usuario_rest::whereNotIn('id', $_usuarios)
            ->where(function ($query) use ($string, $filtros) {
                $query->whereRaw($string, $filtros);
            });
        if ($request->grupo > 0) {
            $query->where('grupo', $request->grupo);
        }
        $resultado = $query->select('id', 'nombre', 'dni', 'botica')->get();

        return response()->json($resultado, 200);
    }

    public function getEventos(Request $request)
    {
        // return $request->all();
        $filtros = [];
        if ($request->filtro1) array_push($filtros, 2);
        if ($request->filtro2) array_push($filtros, 3);
        if ($request->filtro3) array_push($filtros, 4);
        if ($request->filtro4) array_push($filtros, 5);

        $ahora = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $modulos = Abconfig::with('carreras')->select('ab_config.id', 'ab_config.etapa')->get();
        foreach ($modulos as $modulo) {
            $modulo->modulo_selected = false;
            $modulo->carreras_selected = false;
        }
        $_eventos = collect();

        $query = Eventos::where('eventos.tipo_evento_id', $request->tipo)
            ->whereIn('estado_id', $filtros)
            ->where('eventos.fecha_inicio', '>=', $ahora . ' 00:00:00')
            ->select(
                'eventos.id AS evento_id',
                'eventos.titulo',
                'eventos.usuario_id',
                'eventos.link_google_form',
                'eventos.descripcion',
                'eventos.fecha_inicio',
                'eventos.fecha_fin',
                'eventos.estado_id as estado',
                'eventos.duracion',
                'eventos.tipo_evento_id',
                'eventos.report_generated_at'
            );
        if ($request->tipo == 1) {
            $query->join('usuarios', 'usuarios.id', 'eventos.usuario_id')
                ->addSelect('usuarios.nombre', 'usuarios.dni');
        } else if ($request->tipo == 2) {
            $query->join('users', 'users.id', 'eventos.usuario_id')
                ->addSelect('users.name as nombre', 'users.email');
        }
        $eventos = $query->orderBy('eventos.fecha_inicio')->paginate(30);
        foreach ($eventos as $ev) {
            $_eventos->push([
                'evento_id' => $ev->evento_id,
                'titulo' => $ev->titulo,
                'descripcion' => $ev->descripcion,
                'tipo_evento' => $ev->tipo_evento_id,
                'fecha' => date('d-m-Y', strtotime($ev->fecha_inicio)),
                'hora' => date('H:i:s', strtotime($ev->fecha_inicio)) . ' - ' . date('H:i:s', strtotime($ev->fecha_fin)),
                'nombre' => $ev->nombre,
                'estado' => $ev->estado,
                'report_generated_at' => $ev->report_generated_at,
                'duracion' => $ev->duracion,
                'dni' => $ev->dni,
                'email' => $ev->email
            ]);
        }
        $paginate = array(
            'total_paginas' => $eventos->lastPage()
        );
        return response()->json(compact('_eventos', 'modulos', 'paginate'), 200);
    }

    public function crearEventoEnVivo(Request $request)
    {
        // return $request->all();
        $hr_inicio = new Carbon($request->fecha_inicio . ':00');
        $horas = intval($request->duracion / 60);
        $minutos = $request->duracion % 60;
        $hr_inicio->addHours($horas);
        $hr_inicio->addMinutes($minutos);
        $hr_fin = $hr_inicio->format('Y-m-d H:i:s');

        $evento = new Eventos();
        $evento->titulo = $request->titulo;
        $evento->link_vimeo = $request->link;
        $evento->descripcion = $request->descripcion;
        $evento->duracion = $request->duracion;
        $evento->fecha_inicio = $request->fecha_inicio;
        $evento->fecha_fin = $hr_fin;
        $evento->usuario_id = $request->user_id; //TABLA USERS
        $evento->tipo_evento_id = 2; //EVENTO EN VIVO
        $evento->estado_id = 2; //AGENDADO
        $evento->save();

        // REGISTRAR MODULOS INVITADOS AL EVENTO EN VIVO
        foreach ($request->modulo as $modulo) {
            if ($modulo['modulo_selected']) {
                foreach ($modulo['carreras_selected'] as $carrera) {
                    DB::table('eventos_en_vivo_invitados')->insert([
                        'evento_id' => $evento->id,
                        'carrera_id' => $carrera['id'],
                        'modulo_id' => $modulo['id']
                    ]);
                }
            }
        }

        return response()->json(['msg' => 'Evento en vivo creado correctamente.'], 200);
    }

    public function cambiarEstadoEvento(Request $request)
    {
        $evento = Eventos::find($request->evento_id, ['estado_id', 'hora_inicio_real', 'id']);
        $evento->estado_id = $request->estado;
        if ($request->estado == 3 && $evento->hora_inicio_real == null) {
            $evento->hora_inicio_real = date('Y-m-d H:i:s');
        }
        $evento->save();

        return response()->json(['msg' => 'Estado actualizado correctamente.'], 200);
    }

    public function finalizar_evento_con_webhook(Request $request)
    {
        $meeting_id = $request['payload']['object']['id'];
        $evento = Eventos::where('zoom_meeting_id', $meeting_id)->first();
        if ($evento) {
           $response = Eventos::generarReporte($evento);
           return response()->json($response);
        }
        return response()->json([
            'msg' => "Evento no existe."
        ]);

    }

    public function cambiar_estado_ausente(Request $request)
    {
        $explode = explode('-', $request['payload']['object']['participant']['user_name']);
        if (isset($explode[1])) {
            $userDNI = $explode[1];
            $meeting_id = $request['payload']['object']['id'];

            $usuario = Usuario::where('dni', $userDNI)->first(['id', 'nombre']);
            $evento = Eventos::where('zoom_meeting_id', $meeting_id)->first();

            if ($evento && !$usuario) {
                $msg_log = "<---- El participante X se ha desconectado del evento: $evento->id  ---->";
                \Log::channel('webhooks-zoom')->info($msg_log);
                return;
            }

            if ($usuario && $evento) {
                $ahora = new DateTime();

                $actividad = ActividadEvento::where('evento_id', $evento->id)->where('usuario_id', $usuario->id)->first();
                if (!$actividad) {
                    $data = [
                        'estado' => 0,
                        'cant_ingresos' => 1,
                        'cant_salidas' => 0,
                        'ultimo_ingreso' => null,
                        'ultima_salida' => null,
                        'usuario_id' => $usuario->id,
                        'evento_id' => $evento->id
                    ];
                    $actividad = ActividadEvento::store($data);
                }

                $validar_horas = ActividadEvento::whzoom_validar_horas($actividad, 'ultima_salida');
                $validar_ingresos_salidas = ActividadEvento::whzoom_validar_ingresos_salidas($actividad);
                info("", ['validar_horas' => $validar_horas, "ingresos_salidas" => $validar_ingresos_salidas]);
                if ($validar_horas && $validar_ingresos_salidas) {
                    $data = [
                        'estado' => 0,
                        'cant_salidas' => $actividad->cant_salidas + 1,
                        'ultima_salida' => $ahora,
                    ];
                    ActividadEvento::store($data, $actividad);
                    $msg_log = "<---- El participante $usuario->dni - $usuario->nombre se ha desconectado del evento: $evento->id  ---->";
                    \Log::channel('webhooks-zoom')->info($msg_log);
                }
            }
        }
    }

    public function cambiar_estado_activo(Request $request)
    {
        $explode = explode('-', $request['payload']['object']['participant']['user_name']);

        if (isset($explode[1])) {
            $userDNI = $explode[1];
            $meeting_id = $request['payload']['object']['id'];

            $usuario = Usuario::where('dni', $userDNI)->first(['id', 'nombre']);
            $evento = Eventos::where('zoom_meeting_id', $meeting_id)->first();

            if ($evento && !$usuario) {
                $msg_log = "<---- El participante X ingresó al evento: $evento->id  ---->";
                \Log::channel('webhooks-zoom')->info($msg_log);
                return;
            }
            if ($usuario && $evento) {
                $ahora = new DateTime();

                $actividad = ActividadEvento::where('usuario_id', $usuario->id)->where('evento_id', $evento->id)->first();
                if (!$actividad) {
                    $data = [
                        'estado' => 0,
                        'cant_ingresos' => 0,
                        'cant_salidas' => 0,
                        'ultimo_ingreso' => null,
                        'ultima_salida' => null,
                        'usuario_id' => $usuario->id,
                        'evento_id' => $evento->id
                    ];
                    $actividad = ActividadEvento::store($data);
                }

                $validar_horas = ActividadEvento::whzoom_validar_horas($actividad);
                $validar_ingresos_salidas = ActividadEvento::whzoom_validar_ingresos_salidas($actividad);
                info("", ['validar_horas' => $validar_horas, "ingresos_salidas" => $validar_ingresos_salidas]);
                if ($validar_horas && $validar_ingresos_salidas) {
                    $data = [
                        'estado' => 1,
                        'cant_ingresos' => $actividad->cant_ingresos + 1,
                        'ultimo_ingreso' => $ahora,
                    ];
                    ActividadEvento::store($data, $actividad);
                    $msg_log = "<---- El participante $usuario->dni - $usuario->nombre ingresó al evento: $evento->id  ---->";
                    \Log::channel('webhooks-zoom')->info($msg_log);
                }
            }
        }
    }

    public function verificarAsistencia($meeting_id)
    {
        \Log::info("----------------------------------------------------------------------------------------------------------------------------------------");
        $evento = Eventos::select('id', 'estado_id', 'fecha_inicio', 'fecha_fin', 'duracion')->where('zoom_meeting_id', $meeting_id)->first();
        \Log::info("VERIFICAR MARCADO DE PRIMERA Y SEGUNDA ASISTENCIA DEL EVENTO_ID => $evento->id ");
        $fecha_inicio = new Carbon(date('Y-m-d H:i:s', strtotime($evento->fecha_inicio)));
        $min_primera_asistencia = intval($evento->duracion * 0.2);
        $min_segunda_asistencia = intval($evento->duracion * 0.8);

        $ahora = date('Y-m-d H:i');

        $fecha_fin = $evento->fecha_fin;
        $hora_primera_asistencia = new Carbon(date('Y-m-d H:i', strtotime($evento->fecha_inicio)));
        $hora_segunda_asistencia = new Carbon(date('Y-m-d H:i', strtotime($evento->fecha_inicio)));
        $hora_primera_asistencia->addMinutes($min_primera_asistencia);
        $hora_segunda_asistencia->addMinutes($min_segunda_asistencia);
        $asistentes_activos = ActividadEvento::where('evento_id', $evento->id)->where('estado', 1)->get(['usuario_id']);
        $_as = collect();
        foreach ($asistentes_activos as $as) {
            $_as->push($as['usuario_id']);
        }
        \Log::channel('webhooks-zoom')->info("ASISTENTES ACTIVOS => $_as");
        \Log::channel('webhooks-zoom')->info("AHORA => $ahora - PRIMERA ASISTENCIA => " . $hora_primera_asistencia->format('Y-m-d H:i') . " - SEGUNDA ASISTENCIA => " . $hora_segunda_asistencia->format('Y-m-d H:i'));
        // VERIFICAR SI LA HORA ACTUAL ES MENOR A LA HORA FIN
        if ($ahora < strtotime($hora_primera_asistencia->format('Y-m-d H:i'))) {
            // VERIFICAR SI ESTAMOS ANTES DEL 20% DE DURACION (PRIMERA ASISTENCIA)
            if ($ahora <= strtotime($hora_primera_asistencia->format('Y-m-d H:i'))) {
                AsistenteEvento::where('evento_id', '=', $evento->id)
                    ->whereIn('usuario_id', $_as)
                    ->update([
                        'primera_asistencia' => $ahora,
                        'segunda_asistencia' => $ahora
                    ]);
                \Log::channel('webhooks-zoom')->info("EVENTO TERMINO ANTES DE PASAR LA PRIMERA ASISTENCIA");
                \Log::channel('webhooks-zoom')->info("SE MARCO LA PRIMERA Y SEGUNDA ASISTENCIA CON => $ahora");
                // VERIFICAR SI ESTAMOS ANTES DEL 80% DE DURACION (SEGUNDA ASISTENCIA)
            } else if ($ahora <= strtotime($hora_segunda_asistencia->format('Y-m-d H:i'))) {
                AsistenteEvento::where('evento_id', '=', $evento->id)->whereIn('usuario_id', $_as)->update(['segunda_asistencia' => $ahora]);
                \Log::channel('webhooks-zoom')->info("EVENTO TERMINO ANTES DE PASAR LA SEGUNDA ASISTENCIA");
                \Log::channel('webhooks-zoom')->info("SE MARCO LA SEGUNDA ASISTENCIA CON => $ahora");
            }
        }
        \Log::info("----------------------------------------------------------------------------------------------------------------------------------------");
    }

    public function getDetallesxEvento($evento_id)
    {
        $evento = Eventos::find($evento_id);
        $creador = Usuario_rest::find($evento->usuario_id);

        if ($evento->tipo_evento_id == 1) {
            $asistentes = AsistenteEvento::where('evento_id', $evento_id)
                ->join('usuarios', 'usuarios.id', 'asistentes_evento.usuario_id')
                ->select('usuarios.nombre', 'usuarios.dni')
                ->get();
        } else if ($evento->tipo_evento_id == 2) {
            $asistentes = DB::table('eventos_en_vivo_invitados')
                ->join('carreras', 'carreras.id', 'eventos_en_vivo_invitados.carrera_id')
                ->join('ab_config as ab', 'ab.id', 'carreras.config_id')
                ->where('evento_id', $evento->id)
                ->select('ab.etapa', 'carreras.nombre')
                ->get();
            $asistentes = $asistentes->groupBy('etapa');
            $asistentes = $asistentes->toArray();
        }

        return response()->json(compact('evento', 'creador', 'asistentes'), 200);
    }

    public function exportarEventosApp($creador_id)
    {
        $creador = Usuario::find($creador_id, ['dni']);

        $filtros = ['creador_id' => $creador_id];
        $eventos_export = new EventosExport($filtros);
        $eventos_export->view();
        $random = rand(0, 10000);
        $date = date('mdY');
        ob_end_clean();
        ob_start();
        return Excel::download($eventos_export, "Eventos_" . $creador->dni . "_" . $date . "_" . $random . ".xlsx");
    }

    public function exportarAsistentesEventosApp($creador_id)
    {
        $creador = Usuario::find($creador_id, ['dni']);

        $filtros = ['creador_id' => $creador_id];
        $eventos_asistentes_export = new EventosAsistentesExport($filtros);
        $eventos_asistentes_export->view();
        $random = rand(0, 10000);
        $date = date('mdY');
        ob_end_clean();
        ob_start();
        return Excel::download($eventos_asistentes_export, "EventosAsistentes_" . $creador->dni . "_" . $date . "_" . $random . ".xlsx");
    }

    public function eliminarEventoPreReservado($evento_id)
    {
        Eventos::destroy($evento_id);
        return response()->json(['msg' => 'Evento pre-reservado eliminado.'], 200);
    }


    public function eliminarEvento(Request $request)
    {
        /**
         * CAMBIAR EVENTO A ESTADO CANCELADO (5)
         */
        $evento = Eventos::where('id', $request->evento_id)->select('id', 'estado_id', 'cuenta_zoom_id')->first();
        $cuenta_zoom = CuentaZoom::find($evento->cuenta_zoom_id);

        \Log::channel('eventos_virtuales')->info("cuenta zoom del evento a eliminar => $cuenta_zoom");
        if ($evento) {
            switch ($evento->estado_id) {
                case 2:
                    $client = new Client();
                    try {
                        $response = $client->delete('https://api.zoom.us/v2/meetings/' . $evento->zoom_meeting_id, [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Bearer ' . $cuenta_zoom->refresh_token
                                // 'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJxdlZBUE1FSVFRaWFjZXhPRHdwM3V3IiwiZXhwIjoxNjAyMTcyMTY5fQ.0_UF4LUqk18pyi9VDZJ8gDVtDAbW9vnyooWWYZLprAc'
                            ],
                            // 'body' => json_encode($data)
                        ]);
                        $body = json_decode($response->getBody());
                        \Log::channel('eventos_virtuales')->info("respuesta de zoom $body");
                    } catch (RequestException $e) {
                        \Log::channel('eventos_virtuales')->info("catch de zoom" . $e->getResponse()->getBody());
                        $zoomApi = new ZoomApi();
                        $access_token = $zoomApi->generateJWTKey($cuenta_zoom->id);
                        $this->eliminarEvento($request);
                    }

                    $evento->estado_id = 5; //CANCELADO
                    $evento->save();
                    return response()->json(['msg' => 'Evento eliminado.', 'error' => false], 200);
                    break;
                case 3:
                    return response()->json(['msg' => 'No se puede eliminar un evento que ya ha sido iniciado.', 'error' => true], 200);
                    break;
            }
        } else {
            return response()->json(['msg' => 'Ya no existe el evento seleccionado.', 'error' => true], 200);
        }

    }

    public function listado_grupos()
    {
        $_grupos = collect();
        $obj = (object)array('id' => 0, 'valor' => 'NINGUNO');
        $_grupos->push($obj);
        $grupos = Criterio::where('tipo_criterio_id', 1)->select('id', 'valor')->get();
        foreach ($grupos as $gr) {
            $_grupos->push($gr);
        }
        return response()->json(['grupos' => $_grupos], 200);
    }

    public function parseUsuariosNoAgregados($usuarios)
    {
        $data = [];

        foreach ($usuarios as $key => $dni) {
            $data[] = [
                'dni' => $dni,
                'error' => 'Ya no disponible en este horario.'
            ];
        }

        return $data;
    }

    public function evento_estado($evento_id)
    {
        $evento = Eventos::with('estado')->where('id', $evento_id)->select('estado_id')->first();
        if ($evento) {
            return response()->json(['error' => false, 'data' => $evento->estado->estado], 200);
        }
        return response()->json(['error' => true, 'msg' => 'El evento no se ha encontrado'], 500);
    }

    public function descargarEventosEnVivo(Request $request)
    {
        $data = $request->get('params');
        $estados = $data['filtro'];

        // if ($request->filtro1 == 'true') array_push($estados, 2);
        // if ($request->filtro2 == 'true') array_push($estados, 3);
        // if ($request->filtro3 == 'true') array_push($estados, 4);
        // if ($request->filtro4 == 'true') array_push($estados, 5);

        $export = new AulasVirtualesExport($estados,true);
        $export->view();

        $date = date('Y-m-d');
        $filename = "Aulas Virtuales - {$date}.xlsx";
        ob_end_clean();
        ob_start();
        return Excel::download($export, $filename);
    }
}
