<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederSegmentType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'segment',
            'type' => 'type',
            'code' => 'direct-segmentation',
            'name' => 'Segmentación directa',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'segment',
            'type' => 'type',
            'code' => 'segmentation-by-document',
            'name' => 'Segmentación por documento',
            'active' => ACTIVE,
            'position' => 1,
        ]);
    }
}
