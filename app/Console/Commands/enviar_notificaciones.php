<?php

namespace App\Console\Commands;

use App\Models\Error;
use App\Models\User;
use App\Models\Usuario;
use App\Models\PushNotification;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class enviar_notificaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:enviar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar chunks de cada notificación con estado_envio 1 (PENDIENTE) o 2 (EN PROGRESO),
                            de acuerdo a la hora de envío del chunk';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(" Inicio: " . now());
//        info(" Inicio: " . now());

        $notificaciones = PushNotification::whereIn('estado_envio', [1, 2])->get();
//        info($notificaciones->count());
        $ahora = Carbon::now();
        foreach ($notificaciones as $key => $not) {
            $detalles_json = collect(json_decode($not->detalles_json));

            $last_detalle = $detalles_json->max('hora_envio');
            foreach ($detalles_json as $key2 => $detalle) {
//                info($detalle->usuarios);
                if ($detalle->estado_envio == 0) {
                    if ($detalle->hora_envio == $ahora->format('Y-m-d H:i')) {
                        $envio = $this->enviarNotificacion_a_usuarios_x_Chunk($not, $detalle->usuarios);
                        if ($envio) {
//                            $this->info(" Se envió bloque: " . now());
//                            info(" Se envió bloque: " . now());
//                            $detalle->estado_envio = 1;
                        } else {
                            $nueva_fecha = Carbon::createFromFormat('Y-m-d H:i', $last_detalle);
                            $detalle->hora_envio = $nueva_fecha->addMinutes(15)->format('Y-m-d H:i');
                        }
                    }
                }
            }
            $chunk_sin_enviar = $detalles_json->where('estado_envio', 0)->count();
            if ($chunk_sin_enviar == 0) $not->estado_envio = 3;
            $not->detalles_json = json_encode($detalles_json);
            $not->save();

        }
//        info(" Fin: " . now());
        $this->info(" Fin: " . now());
    }

    public function enviarNotificacion_a_usuarios_x_Chunk(PushNotification $notificacion, $usuarios)
    {
        try {
            $usuarios_tokens = User::whereIn('id', $usuarios)
                ->select('id', 'token_firebase')
                ->whereNotNull('token_firebase')
                ->get();
//            info($usuarios_tokens->count());
//            $this->info(" USUARIOS ID: ", $usuarios_tokens->pluck('id'));
//            info(" USUARIOS ID: ", $usuarios_tokens->pluck('id'));
            $resultado = PushNotification::enviar($notificacion->titulo, $notificacion->texto, $usuarios_tokens->pluck('token_firebase'), ["mensaje" => ""]);

            if (isset($resultado['success']) AND isset($resultado['failure'])) {

                $notificacion->success = $notificacion->success + $resultado['success'];
                $notificacion->failure = $notificacion->failure + $resultado['failure'];
                $notificacion->estado_envio = 2;
                $notificacion->save();
                
                return true;

            } else {

                info($resultado);

                return false;
            }
//            $this->info($resultado);
        } catch (\Exception $e) {
//            info($e);
            Error::storeAndNotificateException($e, request());
            return false;
        }
    }
}
