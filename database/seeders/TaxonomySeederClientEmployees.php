<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederClientEmployees extends Seeder
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
            'type' => 'employees',
            'code' => '0-200',
            'name' => '0 - 200 colaboradores',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'client',
            'type' => 'employees',
            'code' => '201-500',
            'name' => '201 - 500 colaboradores',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'client',
            'type' => 'employees',
            'code' => '500-1000',
            'name' => '500 - 1000 colaboradores',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'client',
            'type' => 'employees',
            'code' => '1001-5000',
            'name' => '1001 - 5000 colaboradores',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        
        Taxonomy::create([
            'group' => 'client',
            'type' => 'employees',
            'code' => 'x-5000',
            'name' => 'Más de 5000 colaboradores',
            'active' => ACTIVE,
            'position' => 5,
        ]);


    }
}
