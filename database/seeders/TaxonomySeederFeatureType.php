<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederFeatureType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'feature',
            'type' => 'type',
            'code' => 'boolean',
            'name' => 'Booleano',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'type',
            'code' => 'limit',
            'name' => 'Límite',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'type',
            'code' => 'increment',
            'name' => 'Incremental',
            'active' => ACTIVE,
            'position' => 3,
        ]);


    }
}
