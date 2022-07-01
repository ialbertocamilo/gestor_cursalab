<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use Illuminate\Console\Command;

class UpdateMeetingStartUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:update-url-start {meeting_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar el link de url_start de las reuniones';

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

        $this->info("\n ------- ACTUALIZAR URL START DE LAS REUNIONES - INICIO ------- \n");
        info(" ------- ACTUALIZAR URL START DE LAS REUNIONES - INICIO ------- ");

        if ($meeting_id) {
            $meetings[] = Meeting::with('status', 'account.service')->find($meeting_id);

        } else {
            $hour_start = now()->format('Y-m-d H:i:s');
            $hour_limit = now()->addHour()->format('Y-m-d H:i:s');

            $meetings = Meeting::query()
                // Filtrar las reuniones de la hora
                ->where('starts_at', '>=', $hour_start)
                ->where('starts_at', '<=', $hour_limit)
                // Solo considerar reuniones agendadas
                ->whereHas('status', function ($q) {
                    $q->whereIn('code', ['scheduled']);
                })
                ->get();

        }

        $count_meetings = count($meetings);

        $this->info('Cantidad de meetings: ' . $count_meetings);

        $bar = $this->output->createProgressBar($count_meetings);

        $bar->start();

        $now = now();

        foreach ($meetings as $meeting) {
            // Solo actualiar las que tengan mas de 2 horas
            // desde su ultima actualizacion

            $this->info("now: {$now}");
            info("now: {$now}");
            $this->info("url_start_generated_at: {$meeting->url_start_generated_at}");
            info("url_start_generated_at: {$meeting->url_start_generated_at}");

//            $diff = $now->diffInHours($meeting->url_start_generated_at);
            $diff = $now->diffInMinutes($meeting->url_start_generated_at);
            $this->info("DIFF: {$diff}");
            info("DIFF: {$diff}");

//            if ($diff >= 2) {
//            if ($diff > 120) {
            if ($diff > 60) {
                $meeting->updateMeetingUrlStart();

                $bar->advance();
            }

        }

        $bar->finish();

        $this->info("\n ------- ACTUALIZAR URL START DE LAS REUNIONES - FIN ------- \n");
        info(" ------- ACTUALIZAR URL START DE LAS REUNIONES - FIN ------- ");
    }
}
