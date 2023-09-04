<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\UserBenefit;
use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $user_status_notified = Taxonomy::getFirstData('benefit', 'user_status', 'notified');

        $users_benefit = UserBenefit::whereHas('status', function($q) {
                                    $q->where('code','notify');
                                })
                                ->get();

        $users_benefit->each( function($item) use ($user_status_notified) {
            $benefit = Benefit::whereHas('status', function($q) {
                                        $q->where('code','active');
                                    })
                                    ->where('id',$item?->benefit_id)
                                    ->first();

            if($benefit) {
                $user = User::where('id', $item?->user_id)->select('email')->first();

                Benefit::sendEmail('notify', $user, $benefit);

                $item->status_id = $user_status_notified?->id;
                $item->save();
            }
        });

        // Generate app notifications for each user

        DB::beginTransaction();
        try {

            $grouped_by_benefit = $users_benefit->groupBy('benefit_id');
            foreach ($grouped_by_benefit as $benefit_id => $benefit_users) {
                $users_ids = $benefit_users->pluck('user_id')->toArray();
                $benefit = Benefit::find($benefit_id);

                UserNotification::createNotifications(
                    $benefit->workspace_id,
                    $users_ids,
                    UserNotification::NEW_BENEFIFT,
                    [
                        'benefitName' => $benefit->name
                    ],
                    "beneficio?beneficio=$benefit->id"
                );
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }

        cache_clear_model(UserBenefit::class);

        $this->info("\n ------- Beneficios - Fin ------- \n");
    }

}
