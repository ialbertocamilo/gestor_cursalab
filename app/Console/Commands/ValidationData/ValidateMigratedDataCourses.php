<?php

namespace App\Console\Commands\ValidationData;

// use App\Models\SummaryTopic;
// use App\Models\Course;
use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;

class ValidateMigratedDataCourses extends ValidateMigratedData
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:validate-migrated-data-content-courses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setInitMessage();
        
        $this->renderCoursesTable();
        $this->renderTopicsTable();

        $this->setFinalMessage();
    }


    public function renderCoursesTable()
    {
        $this->line("Cursos: " . now());

        $db = self::connect();
        $data = [];

        $v2_total = DB::table('courses')->count();
        $v2_total_active = DB::table('courses')->where('active', 1)->count();
        $v2_total_inactive = DB::table('courses')->where('active', '!=', 1)->count();
        // $total_evaluable = DB::table('courses')->whereNotNull('deleted_at')->count();

        $v1_total = $db->getTable('cursos')->count();
        $v1_total_active = $db->getTable('cursos')->where('estado', 1)->count();
        $v1_total_inactive = $db->getTable('cursos')->where('estado', '!=', 1)->count();

        $this->addRowTable($data, 'Total de registros', $v1_total, $v2_total);
        $this->addRowTable($data, 'Total activos', $v1_total_active, $v2_total_active);
        $this->addRowTable($data, 'Total inactivos', $v1_total_inactive, $v2_total_inactive);
        // $this->addRowTable($data, 'Total evaluables', $v1_total_deleted, $total_deleted);

        $this->table(
            ['Cursos', 'v1', 'v2', 'status'],
            $data,
        );

        $this->line("-------------------------------");
    }

    public function renderTopicsTable()
    {
        $this->line("Temas: " . now());

        $db = self::connect();
        $data = [];

        $v2_total = DB::table('topics')->count();
        $v1_total = $db->getTable('posteos')->count();

        $v2_total_active = DB::table('topics')->where('active', 1)->count();
        $v1_total_active = $db->getTable('posteos')->where('estado', 1)->count();

        $v2_total_inactive = DB::table('topics')->where('active', '!=', 1)->count();
        $v1_total_inactive = $db->getTable('posteos')->where('estado', '!=', 1)->count();
        
        $v2_total_evaluable = DB::table('topics')->where('assessable', 1)->count();
        $v1_total_evaluable = $db->getTable('posteos')->where('evaluable', 'si')->count();

        
        $this->addRowTable($data, 'Total de registros', $v1_total, $v2_total);
        $this->addRowTable($data, 'Total activos', $v1_total_active, $v2_total_active);
        $this->addRowTable($data, 'Total inactivos', $v1_total_inactive, $v2_total_inactive);
        $this->addRowTable($data, 'Total evaluables', $v1_total_evaluable, $v2_total_evaluable);
        // $this->addRowTable($data, 'Total evaluables', $v1_total_deleted, $total_deleted);

        $this->table(
            ['Temas', 'v1', 'v2', 'status'],
            $data,
        );

        $this->line("-------------------------------");
    }
}
