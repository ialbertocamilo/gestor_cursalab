<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederQuestionType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'question',
            'type' => 'type',
            'code' => 'select-options',
            'name' => 'Selección',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'question',
            'type' => 'type',
            'code' => 'order-options',
            'name' => 'Ordenamiento',
            'active' => INACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'question',
            'type' => 'type',
            'code' => 'written-answer',
            'name' => 'Respuesta escrita',
            'active' => INACTIVE,
            'position' => 3,
        ]);
    }
}
