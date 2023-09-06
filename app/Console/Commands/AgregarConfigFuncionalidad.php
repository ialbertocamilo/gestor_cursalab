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
    protected $signature = 'funcionalidades:add-config {functionality} {--main_menu=} {--side_menu=} {--config=} {--name_func=} {--name_conf=}';
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
        $config = $this->option('config');
        $name_func = $this->option('name_func');
        $name_conf = $this->option('name_conf');

        // Agregar una configuración a una funcionalidad

        $c_functionality = Taxonomy::getFirstData('system','functionality', $functionality);

        if($c_functionality)
            $c_functionality_id = $c_functionality?->id;
        else
            $c_functionality_id = $this->createTaxonomy('functionality', $functionality, $name_func);

        if($side_menu)
            $this->addConfigFunctionality($c_functionality_id, 'side_menu', $side_menu, $name_conf);

        if($main_menu)
            $this->addConfigFunctionality($c_functionality_id, 'main_menu', $main_menu, $name_conf);

        if($config)
            $this->addConfigFunctionality($c_functionality_id, 'config', $config, $name_conf);

    }

    private function createTaxonomy($type, $code, $name = null)
    {
        $id_tax = null;

        try {

            DB::beginTransaction();
            $create_tax = Taxonomy::create([

                'group'=> 'system',
                'type'=> $type,
                'code'=> $code,
                'active' => 1,
                'name' => $name ?? $code

            ]);
            $id_tax = $create_tax?->id;

            $this->info('-- Se creó la taxonomía: '. $code . ' --');

            DB::commit();
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            abort(errorExceptionServer());
        }
        cache_clear_model(Taxonomy::class);

        return $id_tax;
    }

    private function addConfigFunctionality($functionality, $config, $config_value, $name = null)
    {
        $c_config = Taxonomy::getFirstData('system', $config, $config_value);

        if($c_config)
            $config_id = $c_config?->id;
        else
            $config_id = $this->createTaxonomy($config, $config_value, $name);

        $id_func = FunctionalityConfig::where([
            'config_id'=>$config_id,
            'functionality_id' => $functionality
        ])->first();

        if(is_null($id_func))
        {
            try {

                DB::beginTransaction();

                    FunctionalityConfig::create([

                        'config_id'=>$config_id,
                        'functionality_id' => $functionality

                    ]);
                    $this->info('-- Se vinculó la configuración a la funcionalidad --');

                DB::commit();
            } catch (\Exception $e) {
                info($e);
                DB::rollBack();
                abort(errorExceptionServer());
            }
            cache_clear_model(FunctionalityConfig::class);
        }
        else {
            $this->info('-- La relación ya existe --');
        }
    }

}
