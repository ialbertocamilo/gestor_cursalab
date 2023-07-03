<?php

namespace App\Console\Commands;

use App\Models\Benefit;
use Illuminate\Console\Command;

class BeneficiosChangeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beneficios:change-status';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cambia los estados de los beneficios (Active, locked, finished, released)';

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
        info(" ------- Beneficios - Inicio ------- ");

        $beneficios = Benefit::whereHas('status', function($q) {
                            $q->where('code','<>','released');
                        })
                        ->orWhereNull('status_id')
                        ->get();
        foreach ($beneficios as $ben) {
            $beneficio = Benefit::setStatus($ben);
        }

        $this->info("\n ------- Beneficios - Fin ------- \n");
        info(" ------- Beneficios - Fin ------- ");
    }

}
