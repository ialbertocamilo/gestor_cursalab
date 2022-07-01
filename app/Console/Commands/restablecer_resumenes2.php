<?php

namespace App\Console\Commands;

use App\Models\Ev_abierta;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Usuario;
use App\Models\Visita;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class restablecer_resumenes2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:restablecer2';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar registros en las tablas resumen_general y resumen_curso (se actualizan intentos, rankings, promedios)';

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
        $this->update_resumen();
        // $this->update_resumen_2();

    }

    private function update_resumen_2(){
        $rgs = Resumen_general::where('actualizado', 0)->get();
        // $rgs = Resumen_general::select('id', 'usuario_id')->where('usuario_id', 7432)->get();
        $procesar = count($rgs);
        $i = 1;
        foreach ($rgs as $rg) {
            $this->info($i . ' de ' . $procesar . ' ~ u: ' . $rg->usuario_id);
            $i++;
            $tot_completed_gen = $rg->tot_completados;
            $general_asignados = $rg->cur_asignados;
            $percent_general = ($general_asignados > 0) ? (($tot_completed_gen / $general_asignados) * 100) : 0;
            $percent_general = ($percent_general > 100) ? 100 : $percent_general; // maximo porcentaje = 100
            $rg->porcentaje = round($percent_general);
            $rg->save();
        }
    }
    private function update_resumen()
    {
        $rgs = Resumen_general::select('id', 'usuario_id')->where('actualizado', 0)->get();
        // $rgs = Resumen_general::select('id', 'usuario_id')->where('usuario_id', 7432)->get();
        $procesar = count($rgs);
        $i = 1;
        $help = new HelperController();
        foreach ($rgs as $rg) {
            $this->info($i . ' de ' . $procesar . ' ~ u: ' . $rg->usuario_id);
            $i++;
            $cur_asignados =  $help->help_cursos_x_matricula_con_cursos_libre($rg->usuario_id);
            // Limpa registros que no pertenecen a su curricula
            Resumen_x_curso::where('usuario_id',$rg->usuario_id)->whereNotIn('curso_id',$cur_asignados)->update([
                'estado_rxc'=> 0
            ]);
            Resumen_x_curso::where('usuario_id',$rg->usuario_id)->whereIn('curso_id',$cur_asignados)->update([
                'estado_rxc'=> 1
            ]);
            $resume = new RestAvanceController();
            $max_intentos = 3;
            foreach ($cur_asignados as $curso_id) {
                $resume->actualizar_resumen_x_curso($rg->usuario_id, $curso_id, $max_intentos);
            }
            $resume->actualizar_resumen_general($rg->usuario_id);
            Resumen_general::where('usuario_id', $rg->usuario_id)->update(array(
                'actualizado' => 1
            ));
        }

        // Log::info('-- Finaliza proceso --');
        $this->info('-- Finaliza proceso --');
    }

    // Actualizar intentos
    // private function intentos_curso($usuario_id, $curso_id)
    // {
    //     $suma_intentos = Prueba::select(DB::raw('SUM(intentos) as intentos'))->where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->first();
    //     Resumen_x_curso::where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->update(array('intentos' => $suma_intentos->intentos));
    // }

    //////////////////////////////// SUB FUNCIONES ///////////////////////////////

    // Rellenar tabla de cursos para actualizar los RESUMENES (Se ejecuta una sola vez para implementar los nuevos resumenes)
    // public function set_data_update_resumenes()
    // {
    //     $rs = Resumen_x_curso::select(DB::raw('curso_id, COUNT(curso_id) cant'))->groupBy('curso_id')->get();
    //     foreach ($rs as $r) {
    //         echo ('r' . $r->curso_id . '</br>');
    //         DB::table('update_resumenes')->insert([
    //             'curso_id' => $r->curso_id,
    //             'cantidad' => $r->cant
    //         ]);
    //     }
    // }

}
