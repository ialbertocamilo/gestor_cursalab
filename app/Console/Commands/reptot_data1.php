<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class reptot_data1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reptot:data1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera data1 del reporte total';

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

        $w1 = "";
        $limit = "";

        $res1 = \App\Models\Prueba::select('id')->orderBy('id', 'DESC')->first();
        $res2 = \DB::table('reptot_data1')->select('prueba_id')->orderBy('prueba_id', 'DESC')->first();

        $ult_rt1 = json_decode(json_encode($res2), true);
        $ult_prueba_id = ($res1) ? $res1->id : 0;
        $ult_rt1_id = ($ult_rt1) ? $ult_rt1['prueba_id'] : 0;
        
        $this->info('last prueba_id: '. $ult_rt1_id);
        \Log::info('last prueba_id: '. $ult_rt1_id);

        // Solo inserta si es que hay mas datos en pruebas que en rtd1
        if ($ult_prueba_id > $ult_rt1_id) {
            $limit = "LIMIT 500";
            // $limit = "LIMIT 2000";
            $w1 = "WHERE pu.id >".$ult_rt1_id;
            //\Log::info($ult_prueba_id." - ".$ult_rt1_id);
            
            $data1 = \DB::select( \DB::raw("
                                    SELECT pu.id AS prueba_id, o.id AS posteo_id, u.id AS usuario_id, pu.intentos, pu.nota, pu.rptas_fail, pu.created_at, o.curso_id, o.nombre tema, u.grupo_id, u.perfil_id, u.grupo AS grupo_usu, u.dni, u.nombre, u.sexo, u.botica, u.ultima_sesion
                                    FROM usuarios u 
                                    INNER JOIN pruebas pu ON u.id = pu.usuario_id  
                                    INNER JOIN posteos o ON pu.posteo_id = o.id
                                    ".$w1."
                                    GROUP BY pu.usuario_id, pu.posteo_id
                                    ORDER BY pu.id ASC
                                    ".$limit."
                                "));
            // Si retorna datos, inserta
            if ($data1) {
                $data_ar = json_decode(json_encode($data1), true);
                \DB::table('reptot_data1')->insert($data_ar);
                //\Log::info(" -> Inserta ");
            }
        }

        $this->info('Termina reptot_data1');
        \Log::info('Termina reptot_data1');

    }
}
