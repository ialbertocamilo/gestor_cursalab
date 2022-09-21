<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederSummarySource extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'summary',
            'type' => 'source',
            'code' => 'massive-upload-grades',
            'name' => 'Subida masiva de notas',
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }
}
