<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Models\Curricula;
use App\Models\Usuario_rest;
use App\Models\Curricula_criterio;
use App\Models\Matricula_criterio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiRest\HelperController;

class insert_usuario_cursos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:usuario_cursos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Relaciona los usuarios y cursos en la tabla usuario_cursos según su matricula';

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
        $this->insert_usuario_cursos();
    }

    private function insert_usuario_cursos(){
        $usuarios_migrados =  DB::table('usuario_cursos')->select('usuario_id')->groupBy('usuario_id')->pluck('usuario_id');
        $usuarios = Usuario::select('id')->whereNotIn('id', $usuarios_migrados)->get();
        // $helper = new HelperController();
        $bar = $this->output->createProgressBar(count($usuarios));
        $bar->start();
        for ($i = 0; $i < count($usuarios); $i++) {
            $ids_cursos_asignados = $this->help_cursos_x_matricula($usuarios[$i]->id);
            if (count($ids_cursos_asignados)) {
                foreach ($ids_cursos_asignados as $curso_id) {
                    DB::table('usuario_cursos')->insert([
                        'curso_id' => $curso_id,
                        'usuario_id' => $usuarios[$i]->id
                    ]);
                }
            }
            $bar->advance();
        }
        $bar->finish();
    }

    public function help_cursos_x_matricula($usuario_id)
    {
        $usuario = Usuario_rest::where('id', $usuario_id)->first(['id', 'grupo']);
        $result = collect();
        $matriculas = DB::table('matricula AS m')
            ->where('m.usuario_id', $usuario->id)
            ->where('m.estado', 1)
            ->get(['ciclo_id', 'id', 'carrera_id']);
        foreach ($matriculas as $matricula) {
            $matriculas_criterio = Matricula_criterio::select('criterio_id')->where('matricula_id', $matricula->id)->first();
            if(isset($matriculas_criterio)){
                $criterio_id = $matriculas_criterio->criterio_id;
                $curriculas_criterios = Curricula_criterio::select('curricula_id')->where('criterio_id', $criterio_id)->get();
                foreach ($curriculas_criterios as $curricula_criterio) {
                    $curricula = Curricula::join('cursos', 'cursos.id', 'curricula.curso_id')
                        ->select('ciclo_id', 'curso_id')
                        ->where('cursos.estado', 1)
                        ->where('curricula.id', $curricula_criterio->curricula_id)->first();
                    if (isset($curricula) && $curricula->ciclo_id == $matricula->ciclo_id) {
                        $result->push($curricula->curso_id);
                    }
                }
            }
        }
        return $result->unique()->values()->all();
    }
}
