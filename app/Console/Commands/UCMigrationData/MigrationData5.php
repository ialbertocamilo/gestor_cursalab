<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_6;
use Illuminate\Console\Command;

class MigrationData5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-5';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migración de:
        taxonomies, multimedia, vademecum, videoteca";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        Migration_6::migrateData5();

        $this->info(" Fin: " . now());
        info(" Fin: " . now());
    }
}
