<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Models\Resumen_general;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiRest\HelperController;

class crear_actualizar_resumen_general extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:crear_actualizar_resumen_general';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear o actualizar (solo columna cur_asignados) el registro de la tabla resumen_generar de todos los usuarios';

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
        $usuarios = Usuario::all();
        $countUsuarios = $usuarios->count();
        $res_generales = Resumen_general::all();
        $helper = new HelperController();
        $bar = $this->output->createProgressBar($countUsuarios);
        $bar->start();
        foreach ($usuarios as $key => $usuario) {
            $res_general = $res_generales->where('usuario_id', $usuario->id)->first();
            if ($res_general) {
                $res_general->cur_asignados = count($helper->help_cursos_x_matricula($usuario->id)) ;
            } else {
                $res_general = new Resumen_general();
                $res_general->cur_asignados = count($helper->help_cursos_x_matricula($usuario->id)) ;
                $res_general->tot_completados = 0;
                $res_general->nota_prom = 0;
                $res_general->intentos = 0;
                $res_general->rank = 0;
                $res_general->porcentaje = 0;
                $res_general->usuario_id = $usuario->id;
            }
            $res_general->save();
            $this->info(" RESUMEN ACTUALIZADO :: UID: $usuario->id  cur_asignados = $res_general->cur_asignados");
            $bar->advance();
        }
        $bar->finish();
    }
}
