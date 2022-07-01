<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\Meeting;

class MeetingUpdateAttendaceDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:update-attendance-detail {meeting_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar la asistencia de usuarios';

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

        $this->info("\n ------- ACTUALIZAR ASISTENCIA DE USUARIOS - INICIO ------- \n");
        info(" ------- ACTUALIZAR ASISTENCIA DE USUARIOS - INICIO ------- ");

        if ($meeting_id)
        {
            $meetings[] = Meeting::with('status', 'account.service')->find($meeting_id);

        }else{

            $yesterday = Carbon::now()->subDay();
            
            $timelimit = Carbon::now()->subHour();

            // finished_at 10
            // hora server 10-2 => 9:20
            // hora server 11:15 => 10:15

            $meetings = Meeting::with('status', 'account.service')
                ->whereHas('status', function($q){
                    $q->whereIn('code', ['in-progress', 'finished']);
                })
                ->where('starts_at', '>=', $yesterday)
                ->where(function($query) use ($timelimit){
                    $query->whereNull('finished_at');
                    $query->orWhere(function($q) use ($timelimit){
                        $q->whereNotNull('finished_at');
                        $q->whereColumn('finished_at', '>', 'report_generated_at');
                        $q->where('finished_at', '>', $timelimit);
                    });

                })
                ->get();
        }

        $count_meetings = count($meetings);

        $this->info('Cantidad de meetings: ' . $count_meetings);

        $bar = $this->output->createProgressBar($count_meetings);

        $bar->start();

        foreach ($meetings as $meeting)
        {
            $meeting->updateAttendantsData();

            $bar->advance();
        }

        $bar->finish();

        $this->info("\n ------- ACTUALIZAR ASISTENCIA DE USUARIOS - FIN ------- \n");
        info(" ------- ACTUALIZAR ASISTENCIA DE USUARIOS - FIN ------- ");
    }
}
