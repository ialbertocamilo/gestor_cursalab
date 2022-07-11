<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederPoll extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'poll',
            'type' => 'tipo',
            'code' => 'xcurso',
            'name' => 'Por curso',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'poll',
            'type' => 'tipo',
            'code' => 'libre',
            'name' => 'Libre',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'poll',
            'type' => 'tipo',
            'code' => 'xtema',
            'name' => 'Por tema',
            'active' => INACTIVE,
            'position' => 3,
        ]);


        Taxonomy::create([
            'group' => 'poll',
            'type' => 'tipo-pregunta',
            'code' => 'califica',
            'name' => 'Califica',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'poll',
            'type' => 'tipo-pregunta',
            'code' => 'texto',
            'name' => 'Texto',
            'active' => ACTIVE,
            'position' => 2,
        ]);

    }
}
