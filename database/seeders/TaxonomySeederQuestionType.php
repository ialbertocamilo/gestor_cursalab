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
            'code' => 'select-answer',
            'name' => 'Selección',
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }
}
