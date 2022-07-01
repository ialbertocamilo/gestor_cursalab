<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederSector extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'client',
            'type' => 'sector',
            'code' => 'marketing',
            'name' => 'Marketing',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'client',
            'type' => 'sector',
            'code' => 'agro',
            'name' => 'Agropecuario',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'client',
            'type' => 'sector',
            'code' => 'finances',
            'name' => 'Finanzas',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'client',
            'type' => 'sector',
            'code' => 'technology',
            'name' => 'Tecnología',
            'active' => ACTIVE,
            'position' => 4,
        ]);

       

    }
}
