<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TwoFactorAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twofactor:state {state?}'; //estado 1 activa a todos y 0 deshabilita a todos

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Habilitar 2fa a los usuarios recibe el estado como parámetro ejemplo: php artisan twofactor:state 0 - por defecto sera 0';

    /**
     * Execute the console command.
     *
     * @return int
     */

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currentState = $this->argument('state');
        settype($currentState, "int");

        if($currentState == 1) $this->updateStatesUsers(1);
        else $this->updateStatesUsers(0);

        return Command::SUCCESS;
    }

    public function updateStatesUsers(int $state) 
    {
        $roles = Role::getRolesAdminNames();
        User::whereIs(...$roles)->whereNot('enable_2fa', $state)->update(['enable_2fa' => $state]);
    }
}
