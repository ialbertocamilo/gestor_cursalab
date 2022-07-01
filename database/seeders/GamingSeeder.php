<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class GamingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //  MODOS DE JUEGO

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'mode',
            'code' => 'tourist',
            'name' => 'Turista',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'mode',
            'code' => '',
            'name' => 'Mochilero',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'mode',
            'code' => 'adventure',
            'name' => 'Expedición',
            'active' => ACTIVE,
            'position' => 3,
        ]);


        // ACCIONES

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'action',
            'code' => 'win',
            'name' => 'Ganar',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'action',
            'code' => 'tie',
            'name' => 'Empatar',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'action',
            'code' => 'lose',
            'name' => 'Perder',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'action',
            'code' => 'play',
            'name' => 'Jugar',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        // Hitos

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'milestone',
            'code' => '',
            'name' => 'Partida sin errores',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'milestone',
            'code' => '',
            'name' => 'Premio por partida',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'milestone',
            'code' => '',
            'name' => 'Experiencia de juego',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'milestone',
            'code' => '',
            'name' => 'Partida sin errores',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'gaming',
            'type' => 'milestone',
            'code' => '',
            'name' => 'Conoce la Gaming App',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        // Beneficio

        Taxonomy::create([
            'group' => 'benefit',
            'type' => 'type',
            'code' => 'product',
            'name' => 'Producto',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'benefit',
            'type' => 'type',
            'code' => 'service',
            'name' => 'Servicio',
            'active' => ACTIVE,
            'position' => 2,
        ]);
    }
}
