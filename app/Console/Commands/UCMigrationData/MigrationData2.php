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
    protected $signature = 'uc-migration:migration-data-2 {type}';

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
        $type = $this->argument('type');

        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

        $bar = $this->output;

        if ($type === 'schools')
            Migration_2::migrateEscuelas($bar);

        if ($type === 'update_schools_name')
            Migration_2::migrateEscuelasNombre($bar);

        if ($type === 'courses')
            Migration_2::migrateCursos($bar);

        if ($type === 'course_polls')
            Migration_2::migrateCoursePolls($bar);

        if ($type === 'update_courses_name')
            Migration_2::migrateCursosNombre($bar);

        if ($type === 'topics')
            Migration_2::migrateTemas($bar);

        if ($type === 'media_topics')
            Migration_2::migrateMediaTopics($bar);

        if ($type === 'media')
            Migration_2::insertMultimedia($bar);

        if ($type === 'curricula')
            Migration_2::migrateCurricula($bar);

        if ($type === 'checklist_data')
            Migration_2::migrateChecklistData($bar);

        if ($type === 'checklist_user_data')
            Migration_2::migrateChecklistUserData($bar);

        if  ($type === 'user_actions')
            Migration_2::migrateUserActionsData($bar);

        if ($type === 'videoteca')
            Migration_2::migrateVideotecaData($bar);

        if ($type === 'vademecum')
            Migration_2::migrateVademecumData($bar);

        if  ($type === 'glosario')
            Migration_2::migrateGlosarioData($bar);


        $this->info(" Fin: " . now());
        info(" Fin: " . now());
    }
}
