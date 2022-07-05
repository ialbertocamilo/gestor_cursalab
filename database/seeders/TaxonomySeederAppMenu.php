<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederAppMenu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Videoteca',
            'code' => 'videoteca',
            
        ]);
        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Checklist',
            'code' => 'checklist',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Vademecum',
            'code' => 'vademecun',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Aulas Virtuales',
            'code' => 'aulas_virtuales',
        ]);

        Taxonomy::create([

            'group' => 'system'
            'type' => 'side_menu'
            'name' => 'Subir Link'
            'code' => 'subir_link'
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Glosario',
            'code' => 'glosario',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Cursos libres',
            'code' => 'cursoslibres',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Cursos extracurriculares',
            'code' => 'cursosextra',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Ayuda',
            'code' => 'ayuda',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Preguntas frecuentes',
            'code' => 'preguntas_frecuentes',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Ranking',
            'code' => 'ranking',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Diplomas',
            'code' => 'diplomas',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Encuestas libres',
            'code' => 'encuestas_libres',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Progreso',
            'code' => 'progreso',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Malla',
            'code' => 'malla',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Cursos',
            'code' => 'cursos',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Perfil',
            'code' => 'perfil',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Aulas Virtuales',
            'code' => 'aulas_virtuales',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Preguntas frecuentes',
            'code' => 'preguntas',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Progreso',
            'code' => 'progreso',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Malla',
            'code' => 'malla',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Cursos',
            'code' => 'cursos',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Perfil',
            'code' => 'perfil',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'main_menu',
            'name' => 'Anuncios',
            'code' => 'anuncios',
        ]);

        Taxonomy::create([

            'group' => 'system',
            'type' => 'side_menu',
            'name' => 'Reportes',
            'code' => 'reportes',
        ]);

    }
}
