<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class delete_data_err_masivos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:err_masivos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina a la 1:00 am todos los registros encontrado en la tabla err_masivos';

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
        $this->delete_data_err_masivos();
    }
    private function delete_data_err_masivos(){
        // $fecha =  date('Y-m').'-'.(date('d')-2).' 11:59:59';
        // DB::table('err_masivos')->whereDate('created_at', '<=', $fecha)->delete();
        DB::table('err_masivos')->delete();
    }
}
