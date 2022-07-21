<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_6;
use Illuminate\Console\Command;

class MigrationData7 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-7';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migración de:
    Tickets, Push Notifications, FAQ, Glossaries";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        Migration_6::migrateData7();

        $this->info(" Fin: " . now());
        info(" Fin: " . now());
    }
}
