<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederCriterionType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'criterion',
            'type' => 'type',
            'code' => 'date-range',
            'name' => 'Rango de fechas',
            'active' => ACTIVE,
            'position' => 5,
        ]);
    }
}
