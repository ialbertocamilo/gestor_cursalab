<?php

namespace App\Console\Commands;

use App\Events\ResetSquareEvent;

use Illuminate\Console\Command;


class ResetSquares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gaming:reset';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resetear tablero';

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
        ResetSquareEvent::dispatch([]);

        $this->info("------- DONE! -------");
    }

}
