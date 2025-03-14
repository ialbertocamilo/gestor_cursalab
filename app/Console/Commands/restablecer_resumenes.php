<?php

namespace App\Console\Commands;

use App\Models\Ev_abierta;
use Users;
use App\Models\Ciclo;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Usuario;
use App\Models\Matricula;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Visita;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class restablecer_resumenes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:restablecer';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generando registros en la tabla resumen_cursos y resumen_general';

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
        // $this->update_users();
        $this->actualiza_resumen_segun_avance();
    }

    // Los cursos del usuario que no estan en la tabla resumenes por cursos 
    public function actualiza_resumen_segun_avance()
    {
        // Solo para usuarios que tienen progreso. No valido para usuarios que no tienen ningun 
        // $usuario_resumenes = Resumen_general::select('usuario_id')->where('usuario_id', 17616)->get();
        $usuario_resumenes = Resumen_general::select('usuario_id')->where('actualizado', 0)->orderBy('id', 'DESC')->get();

        $procesar = count($usuario_resumenes);
        $i = 1;

        $restAvance = new RestAvanceController();
        $max_intentos = 3;

        foreach ($usuario_resumenes as $usu_res) {
            $this->info($i . ' de ' . $procesar . ' ~ uid: ' . $usu_res->usuario_id);
            $i++;

            $help = new HelperController();
            $cur_asignados =  $help->help_cursos_x_matricula_con_cursos_libre($usu_res->usuario_id);
            $res_existen = Resumen_x_curso::select('curso_id')->where('usuario_id', $usu_res->usuario_id)->pluck('curso_id');

            foreach ($cur_asignados as $key => $curso_id) {

                $res = Prueba::select('curso_id', 'usuario_id')
                    ->where('usuario_id', $usu_res->usuario_id)
                    ->where('curso_id', $curso_id)
                    ->whereNotIn(
                        'curso_id',
                        $res_existen
                    )
                    ->groupBy('curso_id')
                    ->first();

                if (isset($res)) {
                    $restAvance->actualizar_resumen_x_curso($usu_res->usuario_id, $curso_id, $max_intentos);
                   
                    $this->info('-- prueba -> ' . $curso_id);
                } else {
                    $res2 = Ev_abierta::select('curso_id', 'usuario_id')
                        ->where(
                            'usuario_id',
                            $usu_res->usuario_id
                        )
                        ->where('curso_id', $curso_id)
                        ->whereNotIn(
                            'curso_id',
                            $res_existen
                        )
                        ->groupBy('curso_id')
                        ->first();

                    if (isset($res2)) {
                        $restAvance->actualizar_resumen_x_curso($usu_res->usuario_id, $curso_id, $max_intentos);
                        $this->info('-- ev_abierta -> ' . $curso_id);
                    } else {
                        $res3 = Visita::select('curso_id', 'usuario_id')
                            ->where('usuario_id', $usu_res->usuario_id)
                            ->where('estado_tema', 'revisado')
                            ->where('curso_id', $curso_id)
                            ->whereNotIn('curso_id', $res_existen)
                            ->groupBy('curso_id')
                            ->first();
                        if (isset($res3)) {
                            $restAvance->actualizar_resumen_x_curso($usu_res->usuario_id, $curso_id, $max_intentos);
                            $this->info('-- visita -> ' . $curso_id);
                        }
                    }
                }
            }
            $restAvance->actualizar_resumen_general($usu_res->usuario_id);

            Resumen_general::where('usuario_id', $usu_res->usuario_id)->update(array(
                'actualizado' => 1
            ));
        }
        $this->info('Finaliza proceso');
        $this->info('-------------------');
    }
    public function update_users(){
        $users = Resumen_general::select('usuario_id')->where('actualizado',0)->get();
        
        $bar = $this->output->createProgressBar(count($users));
        $bar->start();
        $i = 1;
        foreach ($users as $user) {
            $h= new HelperController();
            $q_a=count($h->help_cursos_x_matricula($user->usuario_id));
            Resumen_general::where('usuario_id',$user->usuario_id)->update([
                'cur_asignados' =>$q_a, 
                'actualizado'=>1,
            ]);
            $bar->advance();
            $i++;
        }
        $bar->finish();
    }
}
