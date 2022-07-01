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

class restablecer_resumenes2_indiv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:restablecer2_indiv';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar registros en las tablas resumen_general y resumen_curso (se actualizan intentos, rankings, promedios) de un usuario INDIVIDUAL';

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
    }

    private function update_resumen()
    {
        // $rgs = Resumen_general::select('id', 'usuario_id')->where('actualizado', 0)->get();
        $rgs = Resumen_general::whereRaw('tot_completados > cur_asignados')->select('id','usuario_id')->get();
        // $rgs = Resumen_general::select('id', 'usuario_id')->whereIn('usuario_id', $usus_id)->get();
        $this->info($rgs);
        $procesar = count($rgs);
        $i = 1;
        foreach ($rgs as $rg) {
            $this->info($i . ' de ' . $procesar . ' ~ u: ' . $rg->usuario_id);
            $i++;
            // Primero elimina registros que no pertenecen a su curricula
            $help = new HelperController();
            $cur_asignados =  $help->help_cursos_x_matricula($rg->usuario_id);
            Resumen_x_curso::where('usuario_id', $rg->usuario_id)->whereNotIn('curso_id', $cur_asignados)->delete();
            // Consulta los registros a actualizar
            $resumenes_curso = Resumen_x_curso::select('id', 'usuario_id', 'curso_id')->where('usuario_id', $rg->usuario_id)->get();
            // $this->info('curso_id: ' . $res->curso_id);

            $resume = new RestAvanceController();
            foreach ($resumenes_curso as $resumen_curso) {
                $max_intentos = 3;
                $resume->actualizar_resumen_x_curso($resumen_curso->usuario_id, $resumen_curso->curso_id, $max_intentos);
                // $this->intentos_curso($resumen_curso->usuario_id, $resumen_curso->curso_id);
            }

            $resume->actualizar_resumen_general($rg->usuario_id);

            // Resumen_general::where('usuario_id', $rg->usuario_id)->update(array(
            //     'actualizado' => 1
            // ));
        }

        // Log::info('-- Finaliza proceso --');
        $this->info('-- Finaliza proceso --');
    }

    // Actualizar intentos
    private function intentos_curso($usuario_id, $curso_id)
    {
        $suma_intentos = Prueba::select(DB::raw('SUM(intentos) as intentos'))->where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->first();
        Resumen_x_curso::where('usuario_id', $usuario_id)->where('curso_id', $curso_id)->update(array('intentos' => $suma_intentos->intentos));
    }
    public function update_users(){
        $users = Usuario::select('id')->take(100)->get();
        foreach ($users as $user) {
            $h= new HelperController();
            $q_a=count($h->help_cursos_x_matricula($user->id));
            Resumen_general::where('usuario_id',$user->id)->update([
                'cur_asignados' =>$q_a 
            ]);
        }
    }
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
