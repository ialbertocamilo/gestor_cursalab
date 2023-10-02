<?php

namespace App\Console\Commands\ValidationData;

use App\Models\SummaryTopic;
use App\Models\Course;
use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use App\Models\Support\OTFConnection;

class ValidateMigratedData extends Command
{
    // public function __construct()
    // {
    //     $this->db = self::connect();
    // }

    protected function connect()
    {
        $db_data = config('database.connections.mysql_v1');

        return new OTFConnection($db_data);
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:validate-migrated-data';

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
        $this->line("Inicio: " . now());

        $data = [];

        $db = self::connect();

        $total = DB::table('courses')->count();
        $total_without_deleted = DB::table('courses')->whereNull('deleted_at')->count();
        $total_deleted = DB::table('courses')->whereNotNull('deleted_at')->count();

        $v1_total = $db->getTable('cursos')->count();
        $v1_total_without_deleted = 0;
        $v1_total_deleted = 0;



        $this->addRowTable($data, 'Total de registros', $v1_total, $total);
        $this->addRowTable($data, 'Total sin eliminados', $v1_total_without_deleted, $total_without_deleted);
        $this->addRowTable($data, 'Total eliminados', $v1_total_deleted, $total_deleted);

        $this->table(
            ['', 'v1', 'v2', 'status'],
            $data,
        );


        // $courses = Course::disableCache()
        //     ->with([
        //         'topics' => function($q) {
        //             $q->where('active', ACTIVE);
        //             $q->select('id', 'name', 'course_id');
        //         },
        //         'schools' => function($q) {
        //             $q->where('active', ACTIVE);
        //             $q->select('id', 'name', 'scheduled_restarts');
        //         }
        //     ])
        //     ->select('id', 'name', 'scheduled_restarts', 'mod_evaluaciones')
        //     ->where('active', ACTIVE)->get();

        // $bar = $this->output->createProgressBar($courses->count());

        // $records = 0;

        // foreach ($courses as $course) {



            // $bar->advance();
        // }

        // $this->line("");

        // $bar->finish();

        // $this->line("Records updated => " . $records);

        $this->line("Fin: " . now());
    }

    public function addRowTable(&$dataTable, $title, $firstValue, $secondValue)
    {
        $status = $firstValue == $secondValue ? 'ok' : 'failed';

        $dataTable[] = [
            $title, $firstValue, $secondValue, $status,
        ];
    }
}
