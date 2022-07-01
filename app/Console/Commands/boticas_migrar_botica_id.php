<?php

namespace App\Console\Commands;

use App\Models\Botica;
use App\Models\Usuario;
use Maatwebsite\Excel\Excel;
use App\Exports\EventosExport;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiRest\HelperController;

class boticas_migrar_botica_id extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botica:migrar_botica_id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar la columna botica_id con el ID de la tabla boticas ';

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
        $helper = new HelperController();
        $this->info('-- Inicio Proceso--');
        $respuesta = $helper->migracionBoticas();
        $this->info('-- Fin Proceso--');
    }
}
