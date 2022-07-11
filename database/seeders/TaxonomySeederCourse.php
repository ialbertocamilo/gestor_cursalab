<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederCourse extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Estado de Usuario Curso

        Taxonomy::create([
            'group' => 'course',
            'type' => 'user-status',
            'code' => 'aprobado',
            'name' => 'Aprobado',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'course',
            'type' => 'user-status',
            'code' => 'desarrollo',
            'name' => 'Desarrollo',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'course',
            'type' => 'user-status',
            'code' => 'desaprobado',
            'name' => 'Desaprobado',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'course',
            'type' => 'user-status',
            'code' => 'enc_pend',
            'name' => 'Encuesta pendiente',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        // Estado de Usuario Tema

        Taxonomy::create([
            'group' => 'topic',
            'type' => 'user-status',
            'code' => 'revisado',
            'name' => 'Revisado',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'topic',
            'type' => 'user-status',
            'code' => 'aprobado',
            'name' => 'Aprobado',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'topic',
            'type' => 'user-status',
            'code' => 'desaprobado',
            'name' => 'Desaprobado',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'topic',
            'type' => 'user-status',
            'code' => 'realizado',
            'name' => 'Realizado',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'topic',
            'type' => 'user-status',
            'code' => 'por-iniciar',
            'name' => 'Por iniciar',
            'active' => ACTIVE,
            'position' => 5,
        ]);


        // ev_Abiertas fuentes

        // app
        // web

        // pruebas fuentes

        // 0
        // 9
        // reset
        // 4
        // 5
        // 6
        // web

        // app
        // old
        // andro
        // ios

    }
}
