<?php

namespace App\Console\Commands;

use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HistoryReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $url = rtrim(env('REPORTS_BASE_URL'), '/') . '/exportar/users_history';

        $workspace = Workspace::where('slug', 'super-food-holding-peru')->first();
        if (!$workspace) return Command::SUCCESS;

        $modulesIds = Workspace::query()
            ->where('active', 1)
            ->where('parent_id', $workspace->id)
            ->get()
            ->pluck('id')
            ->toArray();

        foreach ($modulesIds as $moduleId) {
            $response = Http::acceptJson()->post($url, [
                'workspaceId' => $workspace->id,
                'modules' => [$moduleId],
                'adminId' => 21494 // <- Definir aqui el id del gestor de SFH
            ]);

            $this->info($response->getStatusCode());
        }

        return Command::SUCCESS;
    }
}
