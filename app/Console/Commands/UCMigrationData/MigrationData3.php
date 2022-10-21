<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\UCMigrationData\Migration_3;
use Illuminate\Console\Command;

class MigrationData3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-3 {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migración de:
        evaluaciones => se une con la tabla users,
        encuestas => pasa a criterios y se elimina,
        visitas => pasa a criterios y se elimina,
        tablas resumen => pasa a criterios y se elimina,";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');

        $output = $this->output;

        if ($type == 'polls')
            Migration_3::migrateEncuestas($output);

        if ($type == 'polls-answers')
            Migration_3::migrateEncuestasRespuestas($output);

        if ($type == 'summary-users')
            Migration_3::migrateSummaryUsers($output);
        
        if ($type == 'summary-courses')
            Migration_3::migrateSummaryCourses($output);
        
        if ($type == 'summary-topics')
            Migration_3::migrateSummaryTopics($output);
    }
}
