<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'post',
            'type' => 'faq',
            'code' => 'faq',
            'name' => 'Preguntas Frecuentes',
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }
}
