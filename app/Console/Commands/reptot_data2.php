<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class reptot_data2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reptot:data2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera data2 del reporte total';

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
        $this->info('Genera reptot_data2');
        \Log::info('Genera reptot_data2');

        // Truncate table
         \DB::table('reptot_data2')->truncate();

        $data2 = \DB::select( \DB::raw("
                SELECT u.config_id, u.grupo_id, u.grupo AS grupo_usu, u.perfil_id, u.dni, u.nombre, u.sexo, u.botica, u.id AS usuario_id  
                FROM usuarios u
                WHERE u.id NOT IN (SELECT usuario_id FROM visitas)
                ORDER BY u.id
                "));

        $data_ar = json_decode(json_encode($data2), true);

        //\Log::info($data_ar);

        // Inserta table
        \DB::table('reptot_data2')->insert($data_ar);

    }
}
