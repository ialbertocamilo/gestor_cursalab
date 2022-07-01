<?php

namespace App\Console\Commands;

use App\Models\Botica;
use App\Models\Usuario;
use Illuminate\Console\Command;

class insert_botica_id extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:botica_id';

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
        $this->insert_botica_id();
    }

    public function insert_botica_id(){
        $users = Usuario::select('config_id','id','botica_id','botica','grupo')
                        ->whereNull('botica_id')
                        ->get();
        $boticas = Botica::all();
        $procesar = count($users);
        $this->info('Inicio');
        $i = 1;
         foreach ($users as $user) {
            $this->info($i . ' de ' . $procesar . ' ~ uid: ' . $user->dni);
            $bot = $boticas->where('nombre',$user->botica)->where('config_id',$user->config_id)->where('criterio_id',$user->grupo)->first();
            if($bot){
                $user->botica_id = $bot->id;
                $user->save();
            }
            $i++;
        }
        $this->info('Fin');
    } 
}
