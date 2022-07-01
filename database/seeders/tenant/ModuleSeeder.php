<?php

namespace Database\Seeders\Tenant;

use App\Models\Module;
use App\Models\Taxonomy;
// use App\Models\Criterion;
// use App\Models\CriterionValue;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $criterion = Criterion::where('code', 'module')->where('active', ACTIVE)->first();

        // $criterion_value = CriterionValue::create([
        //     'name_text' => 'Mi primer módulo',
        //     'active' => ACTIVE,
        //     'criterion_id' => $criterion->id,
        
        //     'position' => 1,
        //     'description' => 'Módulo inicial creado por defecto.',
        // ]);

        Module::create([
            'name' => 'Mi primer módulo',
            'active' => ACTIVE,

            // 'criterion_value_id' => $criterion_value->id,
        
            'position' => 1,
            'description' => 'Módulo inicial creado por defecto.',
        ]);
    }
}
