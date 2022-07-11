<?php

namespace App\Console\Commands\UCMigrationData;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\UCMigrationData\Migration_1;
use App\Models\User;
use Illuminate\Console\Command;

class MigrationData1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc-migration:migration-data-1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Migracion de:
        usuarios => se une con la tabla users,
        carreras => pasa a criterios y se elimina,
        ciclos => pasa a criterios y se elimina,
        grupos => pasa a criterios y se elimina,
        boticas => pasa a criterios y se elimina,
        modulos => pasa a criterios y se elimina,";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $module = Criterion::where('code', 'module')->first();
        $carrera = Criterion::where('code', 'career')->first();
        $ciclo = Criterion::where('code', 'ciclo')->first();
//
//        $carrera = CriterionValue::with([
//            'parents' => function($q) use($module) {
//                $q->select('id', 'value_text', 'criterion_id');
//                $q->where('criterion_id', $module->id);
//            }
//        ])
//            ->select('id', 'value_text')
//            ->where('id', 4)
//            ->first();
//
//        $this->info($module->id);
//
//        $this->info("carrera");
//        $this->info($carrera);

        $user = User::with('criterion_values')
            ->whereHas('criterion_values', fn($q) => $q->whereIn('criterion_id', [1]))
//            ->whereHas('criteria')
//                ->where('id')
            ->first();

        $this->info($user);



        $this->info(" Inicio: " . now());
        info(" Inicio: " . now());

//        Migration_1::migrateData1();

        $this->info(" Fin: " . now());
        info(" Fin: " . now());

    }
}
