<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederTopicEvaluationType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'topic',
            'type' => 'evaluation-type',
            'code' => 'open',
            'name' => 'Abierta',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'topic',
            'type' => 'evaluation-type',
            'code' => 'qualified',
            'name' => 'Calificada',
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }
}
