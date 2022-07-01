<?php

namespace App\Console\Commands;

use App\Events\EnableTableEvent;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;


use App\Notifications\TableEnabled;
use App\Models\User;


class EnableSquares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gaming:enable';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Habilitar tablero';

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
        EnableTableEvent::dispatch(true);

        $users = User::whereNotNull('fcm_token')->get();

        // info('users with fcm_token');
        // info($users);

        Notification::send($users, new TableEnabled());

        // $users->notify(new TableEnabled);

        $this->info("------- DONE! -------");
    }

}
