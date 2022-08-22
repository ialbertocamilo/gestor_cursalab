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
        // POSTS

        Taxonomy::create([
            'group' => 'post',
            'type' => 'section',
            'code' => 'faq',
            'name' => 'Preguntas Frecuentes',
            'active' => ACTIVE,
            'position' => 1,
        ]);


        Taxonomy::create([
            'group' => 'post',
            'type' => 'section',
            'code' => 'ayuda_app',
            'name' => 'Ayuda App',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        // ACTIONS

        Taxonomy::create([
            'group' => 'user',
            'type' => 'action',
            'code' => 'to_train',
            'name' => 'Entrenar',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'action',
            'code' => 'supervise',
            'name' => 'Supervisar',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        // ACTIONS

        Taxonomy::create([
            'group' => 'criterion',
            'type' => 'type',
            'code' => 'default',
            'name' => 'Default',
            'active' => ACTIVE,
            'position' => 1,
        ]);

//        Taxonomy::create([
//            'group' => 'criterion',
//            'type' => 'type',
//            'code' => 'date',
//            'name' => 'Fecha',
//            'active' => ACTIVE,
//            'position' => 2,
//        ]);
//
//        Taxonomy::create([
//            'group' => 'criterion',
//            'type' => 'type',
//            'code' => 'number',
//            'name' => 'Numérico',
//            'active' => ACTIVE,
//            'position' => 3,
//        ]);
//
//        Taxonomy::create([
//            'group' => 'criterion',
//            'type' => 'type',
//            'code' => 'boolean',
//            'name' => 'Booleano',
//            'active' => ACTIVE,
//            'position' => 4,
//        ]);
    }
}
