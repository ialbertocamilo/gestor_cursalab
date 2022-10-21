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


        $this->info("\n Fin: " . now());
        info(" \n Fin: " . now());

    }
}
