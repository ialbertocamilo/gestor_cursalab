<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederChecklist extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'checklist',
            'type' => 'type',
            'code' => 'user_trainer',
            'name' => 'Usuario a Entrenador',
            'active' => 1,
            'position' => 1,
        ]);
        Taxonomy::create([
            'group' => 'checklist',
            'type' => 'type',
            'code' => 'trainer_user',
            'name' => 'Entrenador a Usuario',
            'active' => 1,
            'position' => 2,
        ]);
    }
}
