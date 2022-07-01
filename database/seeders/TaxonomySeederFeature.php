<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederFeature extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'general',
            'name' => 'General',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'media',
            'name' => 'Multimedia',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'courses',
            'name' => 'Cursos',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'support',
            'name' => 'Soporte al Cliente',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'exports',
            'name' => 'Exportaciones',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'imports',
            'name' => 'Procesos Masivos',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'tests',
            'name' => 'Evaluaciones',
            'active' => ACTIVE,
            'position' => 6,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'certifications',
            'name' => 'Certificaciones',
            'active' => ACTIVE,
            'position' => 7,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'polls',
            'name' => 'Encuestas',
            'active' => ACTIVE,
            'position' => 8,
        ]);

        Taxonomy::create([
            'group' => 'feature',
            'type' => 'section',
            'code' => 'others',
            'name' => 'Otros',
            'active' => ACTIVE,
            'position' => 9,
        ]);

    }
}
