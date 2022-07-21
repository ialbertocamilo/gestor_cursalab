<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_6;
use Illuminate\Console\Command;

class MigrationData6 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-6';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migración de:
        meetings, accounts, attendants";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        Migration_6::migrateData6();

        $this->info(" Fin: " . now());
        info(" Fin: " . now());
    }
}
