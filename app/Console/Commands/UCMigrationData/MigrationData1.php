<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_1;
use App\Models\User;
use Illuminate\Console\Command;

class MigrationData1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-1 {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migracion de:
        usuarios => se une con la tabla users,
        carreras => pasa a criterios y se elimina,
        ciclos => pasa a criterios y se elimina,
        grupos => pasa a criterios y se elimina,
        boticas => pasa a criterios y se elimina,
        modulos => pasa a criterios y se elimina,";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');

        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        $bar = $this->output;

        if ($type === 'crud') {
            Migration_1::migrateCrudData($bar);
            $this->info("\n CRUD MIGRATED");
            info("\n CRUD MIGRATED");
        }


        if ($type === 'unificar_carreras') {
            Migration_1::unificarCarreras();
            $this->info("\n CARRERAS FIXED");
            info("\n CARRERAS FIXED");
        }

        if ($type === 'users') {
            Migration_1::migrateUsers($bar);
            $this->info("\n USERS MIGRATED");
            info("\n USERS MIGRATED");
        }

        if ($type === 'criteria_users') {
            Migration_1::migrateCriteriaUser($bar);
            $this->info("\n CRITERIA USERS MIGRATED");
            info("\n CRITERIA USERS MIGRATED");
        }

        if ($type === 'curriculas') {
            Migration_1::insertSegmentacionCarrerasCiclosData($bar);
            $this->info("\n CURRICULAS MIGRATED");
            info("\n CURRICULAS MIGRATED");
        }

        if ($type === 'fix_subworkspace_relation'){
            Migration_1::fixSubworkpsaceIdUsers($bar);
            $this->info("\n fixSubworkpsaceIdUsers FIXED");
            info("\n fixSubworkpsaceIdUsers FIXED");
        }

        if ($type === 'fix_grupo_values_relations'){
            Migration_1::fixGrupoValuesRelationships($bar);
            $this->info("\n fix_grupo_values_relations FIXED");
            info("\n fix_grupo_values_relations FIXED");
        }

        if ($type === 'fix_carrera_values_relations'){
            Migration_1::fixCarreraValuesRelationships($bar);
            $this->info("\n fix_carrera_values_relations FIXED");
            info("\n fix_carrera_values_relations FIXED");
        }

        if ($type === 'fix_type_courses'){
            Migration_1::fixTypeCourses($bar);
            $this->info("\n fix_type_courses FIXED");
            info("\n fix_type_courses FIXED");
        }

        if ($type === 'fix_boticas_relation'){
            Migration_1::fixBoticaRelations($bar);
            $this->info("\n fix_boticas_relation FIXED");
            info("\n fix_boticas_relation FIXED");
        }


        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());

    }
}
