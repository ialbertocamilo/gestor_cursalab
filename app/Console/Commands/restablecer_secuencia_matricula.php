<?php

namespace App\Console\Commands;

use App\Models\Ciclo;
use App\Models\Matricula;
use App\Models\Usuario_rest;
use App\Models\Matricula_criterio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class restablecer_secuencia_matricula extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restablecer:matricula';

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
        // $this->restablecer_secuencia_ciclo();
        $this->restablecer_secuencia_ciclo_v2();
    }
    // CREA MATRICULAS FALTANTES MENORES A SU MATRICULA ACTUAL
    private function restablecer_secuencia_ciclo()
    {
        // $usuarios = Usuario_rest::where('id', 7432)->get();
        $usuarios = Usuario_rest::all();
        //OBTENER 
        $this->info('-- Inicio Proceso--');
        $cantidad = count($usuarios);
        $contador = 1;
        $matr_cri_up= [];
        foreach ($usuarios as $usuario) {
            $this->info($contador.'/'.$cantidad);
            $ci_user = Matricula::select('carrera_id', 'ciclo_id', 'secuencia_ciclo', 'presente')->where('usuario_id', $usuario->id)->get();
            if ($ci_user) {
                $collect_user = collect($ci_user);
                $ciclo_actual = $collect_user->where('presente', 1)->first();
                $carrera_id = $ci_user[0]->carrera_id;
                $ciclos = Ciclo::select('id', 'secuencia')->where('carrera_id', $carrera_id)
                    ->where('estado', 1)->where('secuencia', '<', $ciclo_actual->secuencia_ciclo)->get();
                foreach ($ciclos as $ci) {
                    //AGREGAR SI ES QUE EL CICLO ID NO SE ENCUENTRA 
                    $estado = $collect_user->contains('ciclo_id', $ci->id);
                    if (!($estado) && $ci->secuencia != 0) {
                        $mat = new Matricula();
                        $mat->usuario_id = $usuario->id;
                        $mat->carrera_id= $carrera_id;
                        $mat->ciclo_id=  $ci->id;
                        $mat->secuencia_ciclo = $ci->secuencia;
                        $mat->presente=0;
                        $mat->estado =1;
                        $mat->save();
                        Matricula_criterio::insert([
                            'matricula_id' => $mat->id,
                            'criterio_id' => $usuario->grupo
                        ]);
                    }
                }
            }
            $contador++;
        }
        $this->info('-- Fin Proceso--');
    }
     // CREA MATRICULAS FALTANTES MAYORES A SU MATRICULA ACTUAL CON ESTADO 0
    private function restablecer_secuencia_ciclo_v2()
    {
        // $usuarios = Usuario_rest::all();
        // $usuarios = Usuario_rest::where('id','>',1060)->where('id','<=',35278)->get(['id','grupo']);
        $usuarios = Matricula::select('usuarios.grupo','usuario_id',DB::raw('count(usuario_id) as q_usu'))
        ->join('usuarios','usuarios.id','=','matricula.usuario_id')
        ->groupBy('usuario_id')->havingRaw('q_usu < 2')->get(); 
        //OBTENER 
        $this->info('-- Inicio Proceso--');
        $cantidad = count($usuarios);
        $contador = 1;
        $matr_cri_up= [];
        foreach ($usuarios as $usuario) {
            $this->info($contador.'/'.$cantidad.' u:'.$usuario->usuario_id);
            $ci_user = Matricula::select('carrera_id', 'ciclo_id', 'secuencia_ciclo', 'presente')->where('usuario_id', $usuario->usuario_id)->get();
            if ($ci_user) {
                $collect_user = collect($ci_user);
                $ciclo_actual = $collect_user->where('presente', 1)->first();
                $carrera_id = $ci_user[0]->carrera_id;
                $ciclos = Ciclo::select('id', 'secuencia')->where('carrera_id', $carrera_id)
                    ->where('estado', 1)->where('secuencia', '>', $ciclo_actual->secuencia_ciclo)->get();
                foreach ($ciclos as $ci) {
                    //AGREGAR SI ES QUE EL CICLO ID NO SE ENCUENTRA 
                    $estado = $collect_user->contains('ciclo_id', $ci->id);
                    if (!($estado)) {
                        $mat = new Matricula();
                        $mat->usuario_id = $usuario->usuario_id;
                        $mat->carrera_id= $carrera_id;
                        $mat->ciclo_id=  $ci->id;
                        $mat->secuencia_ciclo = $ci->secuencia;
                        $mat->presente= 0;
                        $mat->estado = 0;
                        $mat->save();
                        Matricula_criterio::insert([
                            'matricula_id' => $mat->id,
                            'criterio_id' => $usuario->grupo
                        ]);
                    }
                }
            }
            $contador++;
        }
        $this->info('-- Fin Proceso--');
    }
}
