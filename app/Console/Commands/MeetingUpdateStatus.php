<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\Meeting;
use App\Models\Taxonomy;

class MeetingUpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de las reuniones al estado que corresponde';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $yesterday = Carbon::now()->subDay();

        $overdue = Taxonomy::getFirstData('meeting', 'status', 'overdue');

        /**
         * SELECCIONAR TODOS LOS EVENTOS QUE NO SE INICIARON (AGENDADOS)
         * Y YA PASÓ UN DÍA
         */

        Meeting::where('finishes_at', '<=', $yesterday)
                ->whereHas('status', function($q){
                    $q->whereIn('code', ['scheduled']);
                    // $q->whereIn('code', ['scheduled', 'in-progress']);
                })
                ->update(['status_id' => $overdue->id]);

        $this->info('Reuniones actualizadas');
    }
}
