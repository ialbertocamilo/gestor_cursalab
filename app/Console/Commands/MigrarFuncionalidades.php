<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use App\Models\FunctionalityConfig;
use App\Models\Taxonomy;
use App\Models\Workspace;
use App\Models\WorkspaceFunctionality;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarFuncionalidades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funcionalidades:migracion-data';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar la funcionalidad por defecto para los Workspaces';

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
        // Agregar la funcionalidad por defecto a los workspaces
        $this->functionalitiesWorkspaces();


        // Agregar side_menu a la funcionalidad por defecto
        $this->functionalitiesConfig();

    }

    private function functionalitiesWorkspaces() {

        $this->info("\n ------- Funcionalidad - Inicio ------- \n");
        info(" ------- Funcionalidad - Inicio ------- ");

        $parents = Workspace::whereNull('parent_id')->pluck('id')->toArray();
        $default = Taxonomy::getFirstData('system','functionality','default');

        $list_created = [];
        foreach($parents as $id) {
            $exist = WorkspaceFunctionality::where('workspace_id',$id)->where('functionality_id', $default?->id)->first();
            if(!$exist) {
                $created = WorkspaceFunctionality::create(array('workspace_id'=>$id, 'functionality_id' => $default?->id));
                array_push($list_created, $created->id);
            }
        }
        cache_clear_model(WorkspaceFunctionality::class);

        $this->info("\n ------- Funcionalidad - Fin ".json_encode($list_created)." ------- \n");
        info(" ------- Funcionalidad - Fin ".json_encode($list_created)."------- ");

    }

    private function functionalitiesConfig() {

        $this->info("\n ------- Funcionalidad Config - Inicio ------- \n");
        info(" ------- Funcionalidad Config - Inicio ------- ");

        $default = Taxonomy::getFirstData('system','functionality','default');
        $side_menu = Taxonomy::where('group', 'system')
                    ->where('type', 'side_menu')
                    ->where('active', 1)
                    ->where('code','<>','glosario')
                    ->where('code','<>','beneficios')
                    ->pluck('id')
                    ->toArray();

        $list_created = [];
        foreach($side_menu as $id) {
            $exist = FunctionalityConfig::where('config_id',$id)->where('functionality_id', $default?->id)->first();
            if(!$exist) {
                $created = FunctionalityConfig::create(array('config_id'=>$id, 'functionality_id' => $default?->id));
                array_push($list_created, $created->id);
            }
        }
        cache_clear_model(FunctionalityConfig::class);

        $this->info("\n ------- Funcionalidad Config - Fin ".json_encode($list_created)." ------- \n");
        info(" ------- Funcionalidad Config - Fin ".json_encode($list_created)."------- ");

    }

}
