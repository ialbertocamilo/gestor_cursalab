<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederPriority extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'general',
            'type' => 'priority',
            'code' => 'very-low',
            'name' => 'Muy Bajo',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'general',
            'type' => 'priority',
            'code' => 'low',
            'name' => 'Bajo',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'general',
            'type' => 'priority',
            'code' => 'normal',
            'name' => 'Normal',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'general',
            'type' => 'priority',
            'code' => 'high',
            'name' => 'Alta',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'general',
            'type' => 'priority',
            'code' => 'very-high',
            'name' => 'Muy Alta',
            'active' => ACTIVE,
            'position' => 5,
        ]);

       

    }
}
