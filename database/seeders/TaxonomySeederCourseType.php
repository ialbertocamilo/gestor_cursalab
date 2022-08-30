<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxonomySeederCourseType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'course',
            'type' => 'type',
            'code' => 'regular',
            'name' => 'Regular',
            'active' => ACTIVE,
            'position' => 1,
        ]);
        Taxonomy::create([
            'group' => 'course',
            'type' => 'type',
            'code' => 'extra-curricular',
            'name' => 'Extracurricular',
            'active' => ACTIVE,
            'position' => 2,
        ]);
        Taxonomy::create([
            'group' => 'course',
            'type' => 'type',
            'code' => 'free',
            'name' => 'Libre',
            'active' => ACTIVE,
            'position' => 3,
        ]);
    }
}
