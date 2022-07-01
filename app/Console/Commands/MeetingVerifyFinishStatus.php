<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Meeting;
use App\Models\Error;

use Carbon\Carbon;

class MeetingVerifyFinishStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meeting:verify-finish-status {meeting_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar si una reunión ya debió haber finalizado para todos';

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

        $this->info("\n ------- VERIFICAR ESTADO FINALIZADO - INICIO ------- \n");
        info(" ------- VERIFICAR ESTADO FINALIZADO - INICIO ------- ");

        $now_diff = Carbon::now()->subMinutes(10);
        $now = now();

        $meetings = Meeting::with('account.service')
                        ->whereHas('status', function($q) { 
                            $q->where('code', 'in-progress'); 
                        })
                        ->where('finishes_at', '<=', $now_diff)
                        ->whereNull('finished_at')
                        ->when($meeting_id, function($q) use ($meeting_id){
                            $q->where('id', $meeting_id);
                        })
                        ->get();


        $count_meetings = count($meetings);

        $this->info('Cantidad de reuniones: ' . $count_meetings);

        $bar = $this->output->createProgressBar($count_meetings);

        $bar->start();

        foreach ($meetings as $meeting)
        {
            $account = $meeting->account;

            $minutos = $meeting->finishes_at ? $meeting->finishes_at->diffForHumans() : '?? minutos';
            $fecha = $meeting->finishes_at ? $meeting->finishes_at->format('d/m/Y G:i a') : '??';

            if ($meeting->finishes_at->diffInHours($now) >= 4) {

                $meeting->finalize();

                $subsection = "Reunión finalizada forzada. Fuera de tiempo por más de 4 horas.";

            } else {

                $subsection = "Reunión en progreso no finalizada. Fuera de tiempo.";

            }
            
            $message = "La reunión #{$meeting->id} debió haber finalizado {$minutos} ({$fecha}) [ Cuenta ID {$account->id} => {$account->email} ] ";

            Error::storeAndNotificateDefault($message, 'Aulas Virtuales', $subsection);

            $bar->advance();
        }

        $bar->finish();

        $this->info("\n ------- VERIFICAR ESTADO FINALIZADO - FIN ------- \n");
        info(" ------- VERIFICAR ESTADO FINALIZADO - FIN ------- ");
    }
}
