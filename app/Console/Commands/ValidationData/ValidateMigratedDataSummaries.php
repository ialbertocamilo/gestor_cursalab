<?php

namespace App\Console\Commands\ValidationData;

// use Illuminate\Console\Command;
use App\Models\SummaryUser;
use App\Models\SummaryCourse;
use App\Models\SummaryTopic;
use App\Models\User;
use Carbon\Carbon;
use DB;

class ValidateMigratedDataSummaries extends ValidateMigratedData
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:validate-migrated-data-summaries';

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

        $this->renderUsersTable();
        $this->renderAdministratorsTable();

        $this->setFinalMessage();
    }

    public function renderUsersTable()
    {
        $this->line("Usuarios: " . now());

        $db = self::connect();
        $data = [];

        $v2_total = SummaryUser::count();
        $v1_total = $db->getTable('resumen_general')->count();

        $v2_total_active = User::onlyClientUsers()->where('active', 1)->count();
        $v1_total_active = $db->getTable('resumen_general')->where('estado', 1)->count();

        $v2_total_inactive = User::onlyClientUsers()->where('active', '!=', 1)->count();
        $v1_total_inactive = $db->getTable('usuarios')->where('estado', '!=', 1)->count();
        
        // $init_data = date('Y') . '-01-01';

        // $v2_total_year = DB::table('users')->whereDate('publish_date', '>=', $init_data)->count();
        // $v1_total_year = $db->getTable('usuarios')->whereDate('publish_date', '>=', $init_data)->count();

        // $v2_total_beforeyear = DB::table('users')->whereDate('publish_date', '<', $init_data)->count();
        // $v1_total_beforeyear = $db->getTable('usuarios')->whereDate('publish_date', '<', $init_data)->count();

        
        $this->addRowTable($data, 'Total de registros', $v1_total, $v2_total);
        $this->addRowTable($data, 'Total activos', $v1_total_active, $v2_total_active);
        $this->addRowTable($data, 'Total inactivos', $v1_total_inactive, $v2_total_inactive);
        // $this->addRowTable($data, 'Total publicados este año', $v1_total_year, $v2_total_year);
        // $this->addRowTable($data, 'Total publicados antes de este año', $v1_total_beforeyear, $v2_total_beforeyear);

        $this->table(
            ['Usuarios', 'v1', 'v2', 'status'],
            $data,
        );

        $this->line("-------------------------------");
    }

    public function renderAdministratorsTable()
    {
        $this->line("Administradores: " . now());

        $db = self::connect();
        $data = [];

        $query = User::withTrashed()->whereRelation('roles', function ($query) {
            $query->where('name', '<>', 'super-user');
        });

        $v2_total = $query->count();
        $v1_total = $db->getTable('users')->count();

        $v2_total_active = $query->whereNull('deleted_at')->count();
        $v1_total_active = $db->getTable('users')->whereNull('deleted_at')->count();

        $v2_total_inactive = $query->whereNotNull('deleted_at')->count();
        $v1_total_inactive = $db->getTable('users')->whereNotNull('deleted_at')->count();
        

        
        $this->addRowTable($data, 'Total de registros', $v1_total, $v2_total);
        $this->addRowTable($data, 'Total sin eliminados', $v1_total_active, $v2_total_active);
        $this->addRowTable($data, 'Total eliminados', $v1_total_inactive, $v2_total_inactive);

        $this->table(
            ['Administradores', 'v1', 'v2', 'status'],
            $data,
        );

        $this->line("-------------------------------");
    }
}
