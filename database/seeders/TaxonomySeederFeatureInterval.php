<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederFeatureInterval extends Seeder
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
            'type' => 'interval',
            'code' => 'limitless',
            'name' => 'Indefinido',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'interval',
            'code' => 'days',
            'name' => 'Días',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'interval',
            'code' => 'months',
            'name' => 'Meses',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'interval',
            'code' => 'years',
            'name' => 'Años',
            'active' => ACTIVE,
            'position' => 4,
        ]);

    }
}
