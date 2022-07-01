<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use Illuminate\Console\Command;

class UpdateSingleMeetingUrlStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:update-single-meeting-url-start {meeting_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $meeting_id = $this->argument('meeting_id');

        $this->info("\n ------- ACTUALIZAR URL START DE LA REUNION {$meeting_id} - INICIO ------- \n");
        info(" ------- ACTUALIZAR URL START DE LA REUNION {$meeting_id} - INICIO ------- ");

        $meeting = Meeting::with( 'account.service')->find($meeting_id);

        if ($meeting) {
            $meeting->updateMeetingUrlStart();
        }

        $this->info("\n ------- ACTUALIZAR URL START DE LA REUNION {$meeting_id} - FIN ------- \n");
        info(" ------- ACTUALIZAR URL START DE LA REUNION {$meeting_id} - FIN ------- ");
    }
}
