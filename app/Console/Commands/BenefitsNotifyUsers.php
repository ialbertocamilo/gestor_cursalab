<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use App\Models\User;
use App\Models\UserBenefit;
use Illuminate\Console\Command;

class BenefitsNotifyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beneficios:notify-users';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un email a los usuarios que quisieron que le notifiquen cuando un beneficio esté disponible.';

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


        $users_benefit = UserBenefit::whereHas('status', function($q) {
                                    $q->where('code','notify');
                                })
                                ->get();

        $users_benefit->each( function($item) {
            $benefit = Benefit::whereHas('status', function($q) {
                                        $q->where('code','active');
                                    })
                                    ->where('id',$item?->benefit_id)
                                    ->first();

            if($benefit) {
                $user = User::where('id', $item?->user_id)->first();

                Benefit::sendEmail('notify', $user, $benefit);
            }
        });

        $this->info("\n ------- Beneficios - Fin ------- \n");
    }

}
