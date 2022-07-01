<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederNotification extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'notification',
            'type' => 'type',
            'code' => 'reminder',
            'name' => 'Recordatorio',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'notification',
            'type' => 'type',
            'code' => 'event',
            'name' => 'Evento',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'notification',
            'type' => 'type',
            'code' => 'alarm',
            'name' => 'Alarma',
            'active' => ACTIVE,
            'position' => 3,
        ]);
    }
}
