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
        $this->setInitMessage();
        // $db = self::connect();

        
        // (new ValidateMigratedDataUsers())->renderUsersTable();

        // $this->renderCoursesTable();
        // $this->renderTopicsTable();
        // $this->renderAnnouncementsTable();

        $this->setFinalMessage();
    }

    public function setInitMessage()
    {
        $this->line("Inicio: " . now());
        $this->line("Database v1 (ORIGIN): " . config('database.connections.mysql_v1.name'));
        $this->line("Database v2 (NEW): " . config('database.connections.mysql.read.database'));
    }

    public function setFinalMessage()
    {
        $this->line("Fin: " . now());
    }

    public function addRowTable(&$dataTable, $title, $firstValue, $secondValue)
    {
        $status = $firstValue == $secondValue ? 'ok' : 'failed';

        $dataTable[] = [
            $title, $firstValue, $secondValue, $status,
        ];
    }



    public function renderAnnouncementsTable()
    {
        $this->line("Anuncios: " . now());

        $db = self::connect();
        $data = [];

        $v2_total = DB::table('announcements')->count();
        $v1_total = $db->getTable('anuncios')->count();

        $v2_total_active = DB::table('announcements')->where('active', 1)->count();
        $v1_total_active = $db->getTable('anuncios')->where('estado', 1)->count();

        $v2_total_inactive = DB::table('announcements')->where('active', '!=', 1)->count();
        $v1_total_inactive = $db->getTable('anuncios')->where('estado', '!=', 1)->count();
        
        $init_data = date('Y') . '-01-01';

        $v2_total_year = DB::table('announcements')->whereDate('publish_date', '>=', $init_data)->count();
        $v1_total_year = $db->getTable('anuncios')->whereDate('publish_date', '>=', $init_data)->count();

        $v2_total_beforeyear = DB::table('announcements')->whereDate('publish_date', '<', $init_data)->count();
        $v1_total_beforeyear = $db->getTable('anuncios')->whereDate('publish_date', '<', $init_data)->count();

        
        $this->addRowTable($data, 'Total de registros', $v1_total, $v2_total);
        $this->addRowTable($data, 'Total activos', $v1_total_active, $v2_total_active);
        $this->addRowTable($data, 'Total inactivos', $v1_total_inactive, $v2_total_inactive);
        $this->addRowTable($data, 'Total publicados este año', $v1_total_year, $v2_total_year);
        $this->addRowTable($data, 'Total publicados antes de este año', $v1_total_beforeyear, $v2_total_beforeyear);

        $this->table(
            ['Anuncios', 'v1', 'v2', 'status'],
            $data,
        );

        $this->line("-------------------------------");
    }
}
