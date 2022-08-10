<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_2;
use Illuminate\Console\Command;

class MigrationData2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migracion de:
        escuelas => schools,
        cursos => courses,
        posteos => topics, ";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        Migration_2::migrateEscuelas();
        Migration_2::migrateCursos();
        Migration_2::migrateTemas();
        Migration_2::migrateCurricula();

        $this->info(" Fin: " . now());
        info(" Fin: " . now());
    }
}
