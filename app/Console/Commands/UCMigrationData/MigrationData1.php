<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_1;
use Illuminate\Console\Command;

class MigrationData1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-1';

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
        // TODO: Migrate usuarios
        // TODO: Migrate carreras
        // TODO: Migrate ciclos
        // TODO: Migrate grupos
        // TODO: Migrate boticas
        // TODO: Migrate modulos
        Migration_1::migrationData();
    }
}
