<?php

namespace App\Console\Commands;

use App\Models\Abconfig;
use App\Models\Resumen_x_curso;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class ActualizarTablasResumen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:actualizar-tablas-por-curso {curso_id}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar registros en las tablas resumen_general y resumen_curso (se actualizan asignados, aprobados, intentos, rankings, promedios)';

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
        $curso_id = $this->argument('curso_id');

        $this->info("\n ------- ACTUALIZAR TABLAS RESUMENES DE LOS USUARIOS QUE TENGAN ASIGNADOS EL CURSO => $curso_id ------- \n");
        Log::info("\n ------- ACTUALIZAR TABLAS RESUMENES DE LOS USUARIOS QUE TENGAN ASIGNADOS EL CURSO => $curso_id ------- \n");

        $usuarios_resumen_x_cursos = Resumen_x_curso::with([
                        'usuario' => function($query) {
                            $query->select('nombre', 'config_id', 'id');
                        }
                    ])
                    ->where('curso_id', $curso_id)
                    ->get();

        $rest_avance_controller = new RestAvanceController();
        $count_usuarios = count($usuarios_resumen_x_cursos);
        $this->info('Cantidad de usuarios: '.$count_usuarios);
        $bar = $this->output->createProgressBar($count_usuarios);

        $bar->start();

        $i = 1;

        foreach ($usuarios_resumen_x_cursos as $usuario_resumen_x_curso)
        {
            $usuario_id = $usuario_resumen_x_curso->usuario_id;

            $config = Abconfig::select('mod_evaluaciones')->where('id', $usuario_resumen_x_curso->usuario->config_id)->first();  
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            // $this->info("\n ACTUALIZAR RESUMEN X CURSO => Usuario ". $usuario_id);

            $rest_avance_controller->actualizar_resumen_x_curso($usuario_id, $curso_id, $mod_eval['nro_intentos']);

            // $this->info("\n ACTUALIZAR RESUMEN GENERAL => Usuario ". $usuario_id);

            $rest_avance_controller->actualizar_resumen_general($usuario_id);

            $bar->advance();

           Log::info($i . ' de ' . $count_usuarios . ' ~ uid: ' . $usuario_id);

            $i++;
        }

        $this->info("\n ------- ACTUALIZAR TABLAS RESUMENES CURSO => $curso_id FIN ------- \n");
        Log::info("\n ------- ACTUALIZAR TABLAS RESUMENES CURSO => $curso_id FIN ------- \n");

        $bar->finish();
    }

}
