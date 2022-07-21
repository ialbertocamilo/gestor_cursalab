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
            'group' => null,
            'type' => 'faq',
            'code' => 'faq',
            'name' => null,
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }
}
