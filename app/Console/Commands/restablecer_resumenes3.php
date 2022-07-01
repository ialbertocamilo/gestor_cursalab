<?php

namespace App\Console\Commands;

use App\Models\Ev_abierta;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Usuario;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Visita;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class restablecer_resumenes3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:restablecer3';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->elimina_no_asignados();
    }

    private function elimina_no_asignados()
    {
        // $rgs = Resumen_general::select('id', 'usuario_id')->where('usuario_id', 27459)->get();
        $rgs = Resumen_general::select('id', 'usuario_id')->where('actualizado', 1)->get();

        $procesar = count($rgs);
        $i = 1;
        $this->info('-- Inicia proceso --');
        foreach ($rgs as $rg) {
            $this->info($i . ' de ' . $procesar);
            $i++;

            $help = new HelperController();
            $cur_asignados =  $help->help_cursos_x_matricula($rg->usuario_id);

            // DELETE RESUMEN_X_CURSO
            Resumen_x_curso::where('usuario_id', $rg->usuario_id)->whereNotIn('curso_id', $cur_asignados)->delete();

            $this->actualizar_intentos_general($rg->usuario_id);

            Resumen_general::where('usuario_id', $rg->usuario_id)->update(array(
                'actualizado' => 0
            ));
        }
        $this->info('-- Finaliza proceso --');
    }

    // Actualizar intentos
    private function actualizar_intentos_general($usuario_id)
    {
        $suma_intentos_x_curso = Resumen_x_curso::select(DB::raw('SUM(intentos) as intentos'))->where('usuario_id', $usuario_id)->first();
        Resumen_general::where('usuario_id', $usuario_id)->update(array('intentos' => $suma_intentos_x_curso->intentos));
    }
}
