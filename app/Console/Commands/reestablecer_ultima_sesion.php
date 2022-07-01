<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;

class reestablecer_ultima_sesion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reestablecer:ultima_sesion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reestablecer ultima sesion con la ultima fecha de la ultima prueba realizada, bajo diferencia de 24 horas';

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
        $usuarios = Usuario::join('pruebas', 'pruebas.usuario_id', 'usuarios.id')
                    ->whereRaw('HOUR( TIMEDIFF( usuarios.ultima_sesion, pruebas.updated_at)) > 24')
                    ->whereRaw('usuarios.ultima_sesion < pruebas.updated_at')
                    ->groupBy('usuarios.id')
                    ->orderBy('pruebas.updated_at', 'DESC')
                    ->select('usuarios.id', 'usuarios.ultima_sesion', 'pruebas.updated_at as pruebas_updated_at')
                    // ->where('usuarios.id', 3263)
                    ->get();
        $usuariosNULL = Usuario::join('pruebas', 'pruebas.usuario_id', 'usuarios.id')
                    // ->whereRaw('HOUR( TIMEDIFF( usuarios.ultima_sesion, pruebas.updated_at)) > 24')
                    // ->whereRaw('usuarios.ultima_sesion < pruebas.updated_at')
                    ->whereNull('usuarios.ultima_sesion')
                    ->groupBy('usuarios.id')
                    ->orderBy('pruebas.updated_at', 'DESC')
                    ->select('usuarios.id', 'usuarios.ultima_sesion', 'pruebas.updated_at as pruebas_updated_at')
                    // ->where('usuarios.id', 3263)
                    ->get();
        $i = 1;
        $this->info('CANT. usuarios =>  '. count($usuarios) );
        
        foreach ($usuarios as $usuario) {
            $this->info($i);
            // $this->info("Usuario ID => $usuario->id ");
            // $this->info("Usuario ULTIMA SESION => $usuario->ultima_sesion ");
            // $this->info("Usuario UPDATED_AT => $usuario->pruebas_updated_at ");
            // $this->info('usuario =>  '. $usuario->updated_at);

            $usuario_update = Usuario::find($usuario->id);
            $usuario_update->ultima_sesion = $usuario->pruebas_updated_at;
            $usuario_update->save();

            // Usuario::where('usuarios.id', $usuario->id)->update([
            //     'ultima_sesion' => $usuario->updated_at
            // ]);
            $i ++;

        }
        $i2 = 1;
        $this->info('CANT. usuarios NULL =>  '. count($usuariosNULL) );

        foreach ($usuariosNULL as $usuario) {
            $this->info($i2);
            // $this->info("Usuario ID => $usuario->id ");
            // $this->info("Usuario ULTIMA SESION => $usuario->ultima_sesion ");
            // $this->info("Usuario UPDATED_AT => $usuario->pruebas_updated_at ");
            // $this->info('usuario =>  '. $usuario->updated_at);

            $usuario_update = Usuario::find($usuario->id);
            $usuario_update->ultima_sesion = $usuario->pruebas_updated_at;
            $usuario_update->save();

            // Usuario::where('usuarios.id', $usuario->id)->update([
            //     'ultima_sesion' => $usuario->updated_at
            // ]);
            $i2 ++;

        }
        $this->info('FIN');
    }
}
