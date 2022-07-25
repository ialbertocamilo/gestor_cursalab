<?php

namespace App\Console\Commands;

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
        $notificaciones = PushNotification::whereIn('estado_envio', [1,2])->get();
        $ahora = Carbon::now();
        foreach ($notificaciones as $key => $not ) {
            $detalles_json = collect(json_decode($not->detalles_json));

            $last_detalle = $detalles_json->max('hora_envio');
            foreach ($detalles_json as $key2 => $detalle) {
                if ($detalle->estado_envio == 0 ){
                    if ($detalle->hora_envio == $ahora->format('Y-m-d H:i')) {
                        $envio = $this->enviarNotificacion_a_usuarios_x_Chunk($not, $detalle->usuarios);
                        if ($envio){
                            $detalle->estado_envio = 1;
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
    }

    public function enviarNotificacion_a_usuarios_x_Chunk(PushNotification $notificacion, $usuarios)
    {
        try {
            $usuarios_tokens = Usuario::whereIn('id', $usuarios)->pluck('token_firebase');
            $resultado = PushNotification::enviar($notificacion->titulo, $notificacion->texto, $usuarios_tokens, ["mensaje"=>""]);
            $notificacion->success = $notificacion->success + $resultado['success'];
            $notificacion->failure = $notificacion->failure + $resultado['failure'];
            $notificacion->estado_envio = 2;
            $notificacion->save();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
