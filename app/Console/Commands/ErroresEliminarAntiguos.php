<?php

namespace App\Console\Commands;

use App\Models\Taxonomia;
use App\Models\Error;

use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErroresEliminarAntiguos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'errores:eliminar-antiguos';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar errores solucionados de más de un mes o errores no solucionados de más de dos meses';

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
        $this->info("\n ------- ELIMINAR ERRORES ANTIGUOS - INICIO ------- \n");
        info(" ------- ELIMINAR ERRORES ANTIGUOS - INICIO ------- ");

        $solved = Error::whereHas('status', function($q){
                            $q->where('code', 'solved');
                        })
                        ->where('created_at', '<=', Carbon::now()->subMonth());

        $solved_count = $solved->count();

        $solved->delete();

        $not_solved = Error::whereHas('status', function($q){
                            $q->where('code', '<>', 'solved');
                        })
                        ->where('created_at', '<=', Carbon::now()->subMonths(2));

        $not_solved_count = $not_solved->count();

        $not_solved->delete();

        $this->info("\n ------- Cantidad de errores solucionados eliminados: {$solved_count} ------- \n");
        info("------- Cantidad de errores solucionados: {$solved_count} -------");

        $this->info("\n ------- Cantidad de errores no solucionados eliminados: {$not_solved_count} ------- \n");
        info("------- Cantidad de errores solucionados: {$not_solved_count} -------");


        $this->info("\n ------- ELIMINAR ERRORES ANTIGUOS - FIN ------- \n");
        info(" ------- ELIMINAR ERRORES ANTIGUOS - FIN ------- ");
    }

}
