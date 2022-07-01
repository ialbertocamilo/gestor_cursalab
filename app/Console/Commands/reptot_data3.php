<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class reptot_data3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reptot:data3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera data3 del reporte total';

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
        $this->info('Genera reptot_data3');
        \Log::info('Genera reptot_data3');

        // Truncate table
         \DB::table('reptot_data3')->truncate();

        $data3 = \DB::select( \DB::raw("
                SELECT u.config_id, u.grupo_id, u.grupo AS grupo_usu, u.perfil_id, u.dni, u.nombre, u.sexo, u.botica, o.categoria_id, o.curso_id, o.nombre tema, u.ultima_sesion, v.sumatoria, u.id AS usuario_id
                FROM usuarios u 
                INNER JOIN visitas v ON u.id = v.usuario_id
                LEFT JOIN posteos o ON v.post_id = o.id
                WHERE u.id IN (SELECT usuario_id FROM visitas)
                AND u.id NOT IN (SELECT usuario_id FROM pruebas)
                ORDER BY u.id
                "));

        $data_ar = json_decode(json_encode($data3), true);

        //\Log::info($data_ar);

        // Inserta table
        \DB::table('reptot_data3')->insert($data_ar);

    }
}
