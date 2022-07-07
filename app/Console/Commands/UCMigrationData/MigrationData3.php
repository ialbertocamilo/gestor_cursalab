<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_1;
use Illuminate\Console\Command;

class MigrationData3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migración de:
        evaluaciones => se une con la tabla users,
        encuestas => pasa a criterios y se elimina,
        visitas => pasa a criterios y se elimina,
        tablas resumen => pasa a criterios y se elimina,"

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Migration_3::migratePruebas();
        Migration_3::migrateEncuestas();
    }
}
