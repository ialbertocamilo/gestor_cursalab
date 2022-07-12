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
//
//        $user = User::query()
//            ->withWhereHas('criterion_values', fn($q) => $q->whereIn('criterion_id', [5]))
////            ->whereHas('criteria')
////                ->where('id')
//            ->first();
//
//        $this->info($user);

        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        Migration_1::migrateData1();

        $this->info(" Fin: " . now());
        info(" Fin: " . now());

    }
}
