<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use App\Models\FunctionalityConfig;
use App\Models\Taxonomy;
use App\Models\Workspace;
use App\Models\WorkspaceFunctionality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AgregarConfigFuncionalidad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funcionalidades:add-config {functionality} {--main_menu=} {--side_menu=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar configuraciones a las funcionalidades';

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
        $functionality = $this->argument('functionality');
        $side_menu = $this->option('side_menu');
        $main_menu = $this->option('main_menu');

        // Agregar una configuración a una funcionalidad

        $c_functionality = Taxonomy::getFirstData('system','functionality', $functionality);
        if($side_menu){
            $c_side_menu = Taxonomy::getFirstData('system','side_menu', $side_menu);
            $this->addConfigFunctionality($c_functionality, $c_side_menu);
        }
        if($main_menu) {
            $c_main_menu = Taxonomy::getFirstData('system','main_menu', $main_menu);
            $this->addConfigFunctionality($c_functionality, $c_main_menu);
        }


    }

    private function addConfigFunctionality($functionality, $config){

        try {

            DB::beginTransaction();
            FunctionalityConfig::create([

                'config_id'=>$config?->id,
                'functionality_id' => $functionality?->id

            ]);

            DB::commit();
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            abort(errorExceptionServer());
        }
        cache_clear_model(FunctionalityConfig::class);
    }

}
