<?php

namespace App\Console\Commands;

use App\Events\EnableTableEvent;

use Illuminate\Console\Command;


class DisableSquares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gaming:disable';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deshabilitar tablero';

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
        EnableTableEvent::dispatch(false);

        $this->info("------- DONE! -------");
    }

}
