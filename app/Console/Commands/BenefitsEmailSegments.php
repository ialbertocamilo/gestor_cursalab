<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use App\Models\EmailSegment;
use App\Models\User;
use App\Models\UserBenefit;
use Illuminate\Console\Command;

class BenefitsEmailSegments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beneficios:email-segments';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un email a los usuarios que fueron segmentados por beneficio';

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
        $this->info("\n ------- Beneficios - Inicio ------- \n");

        $queue = EmailSegment::where('sent', 0)->orderBy('created_at', 'asc')->first();

        if($queue) {

            $users = $queue->users ? json_decode($queue->users) : null;
            if(is_array($users)) {
                foreach($users as $user_id) {
                    $user = User::where('id', $user_id)->select('email')->first();
                    $benefit = Benefit::where('id', $queue->benefit_id)->first();
                    if($user && $benefit) {
                        Benefit::sendEmail('new', $user, $benefit);
                    }
                }
            }
            $queue->sent = true;
            $queue->save();
            cache_clear_model(EmailSegment::class);
        }

        $this->info("\n ------- Beneficios - Fin ------- \n");
    }

}
