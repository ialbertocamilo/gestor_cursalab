<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederSystem extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $master = Taxonomy::create([
            'group' => 'system',
            'type' => 'platform',
            'code' => 'web',
            'name' => 'Web',
            'alias' => 'Web',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        $client = Taxonomy::create([
            'group' => 'system',
            'type' => 'platform',
            'code' => 'app',
            'name' => 'APP',
            'alias' => 'APP',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        // Source

        Taxonomy::create([
            'group' => 'system',
            'type' => 'source',
            'code' => 'internal',
            'name' => 'Interno',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'source',
            'code' => 'external',
            'name' => 'Externo',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        // Models

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'user',
            'path' => 'App\Models\User',
            'name' => 'Usuario',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'server',
            'path' => 'App\Models\Server',
            'name' => 'Servidor',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'role',
            'path' => 'App\Models\Role',
            'name' => 'Rol',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'permission',
            'path' => 'App\Models\Permission',
            'name' => 'Permiso',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'question',
            'path' => 'App\Models\Question',
            'name' => 'Pregunta',
            'active' => ACTIVE,
            'position' => 6,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'option',
            'path' => 'App\Models\Option',
            'name' => 'Opción',
            'active' => ACTIVE,
            'position' => 6,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'platform',
            'path' => 'App\Models\Platform',
            'name' => 'Plataforma',
            'active' => ACTIVE,
            'position' => 7,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'benefit',
            'path' => 'App\Models\Benefit',
            'name' => 'Beneficio',
            'active' => ACTIVE,
            'position' => 8,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'course',
            'path' => 'App\Models\Course',
            'name' => 'Curso',
            'active' => ACTIVE,
            'position' => 9,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'client',
            'path' => 'App\Models\Client',
            'name' => 'Cliente',
            'active' => ACTIVE,
            'position' => 10,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'tag',
            'path' => 'App\Models\Tag',
            'name' => 'Tag',
            'active' => ACTIVE,
            'position' => 11,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'Taxonomy',
            'path' => 'App\Models\Taxonomy',
            'name' => 'Taxonomía',
            'active' => ACTIVE,
            'position' => 12,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'notification-type',
            'path' => 'App\Models\NotificationType',
            'name' => 'Tipo de Notificación',
            'active' => ACTIVE,
            'position' => 13,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'media',
            'path' => 'App\Models\Media',
            'name' => 'Multimedia',
            'active' => ACTIVE,
            'position' => 14,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'audit',
            'path' => 'App\Models\Audit',
            'name' => 'Auditoría',
            'active' => ACTIVE,
            'position' => 15,
        ]);
        
        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'recipient',
            'path' => 'App\Models\NotificationRecipient',
            'name' => 'Destinatario',
            'active' => ACTIVE,
            'position' => 16,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'announcement',
            'path' => 'App\Models\Announcement',
            'name' => 'Anuncio',
            'active' => ACTIVE,
            'position' => 17,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'error',
            'path' => 'App\Models\Error',
            'name' => 'Log de Eventos',
            'active' => ACTIVE,
            'position' => 18,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'setting',
            'path' => 'App\Models\Setting',
            'name' => 'Configuración',
            'active' => ACTIVE,
            'position' => 19,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'school',
            'path' => 'App\Models\School',
            'name' => 'Escuela',
            'active' => ACTIVE,
            'position' => 20,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'topic',
            'path' => 'App\Models\Topic',
            'name' => 'Tema',
            'active' => ACTIVE,
            'position' => 21,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'domain',
            'path' => 'App\Models\Domain',
            'name' => 'Dominio',
            'active' => ACTIVE,
            'position' => 22,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'gaming-score',
            'path' => 'App\Models\Gaming\Score',
            'name' => 'Puntaje',
            'active' => ACTIVE,
            'position' => 23,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'model',
            'code' => 'gaming-level',
            'path' => 'App\Models\Gaming\Level',
            'name' => 'Nivel',
            'active' => ACTIVE,
            'position' => 23,
        ]);




        // Database actions

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'created',
            'name' => 'Creación',
            'active' => ACTIVE,
            'position' => 1,
            'icon' => 'add',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'updated',
            'name' => 'Actualización',
            'active' => ACTIVE,
            'position' => 2,
            'icon' => 'edit',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'deleted',
            'name' => 'Eliminación',
            'active' => ACTIVE,
            'position' => 3,
            'icon' => 'delete',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'synced',
            'name' => 'Sincronización (S)',
            'active' => ACTIVE,
            'position' => 4,
            'icon' => 'sync',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'attached',
            'name' => 'Sincronización (A)',
            'active' => ACTIVE,
            'position' => 5,
            'icon' => 'sync',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'toggled',
            'name' => 'Sincronización (T)',
            'active' => ACTIVE,
            'position' => 6,
            'icon' => 'sync',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'event',
            'code' => 'detached',
            'name' => 'Sincronización (D)',
            'active' => ACTIVE,
            'position' => 7,
            'icon' => 'sync',
        ]);



        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'created',
            'name' => 'Creó',
            'active' => ACTIVE,
            'position' => 1,
            'icon' => 'add',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'updated',
            'name' => 'Actualizó',
            'active' => ACTIVE,
            'position' => 2,
            'icon' => 'edit',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'deleted',
            'name' => 'Eliminó',
            'active' => ACTIVE,
            'position' => 3,
            'icon' => 'delete',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'synced',
            'name' => 'Actualizó (S)',
            'active' => ACTIVE,
            'position' => 4,
            'icon' => 'sync',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'attached',
            'name' => 'Actualizó (A)',
            'active' => ACTIVE,
            'position' => 5,
            'icon' => 'sync',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'toggled',
            'name' => 'Actualizó (T)',
            'active' => ACTIVE,
            'position' => 6,
            'icon' => 'sync',
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'action',
            'code' => 'detached',
            'name' => 'Actualizó (D)',
            'active' => ACTIVE,
            'position' => 7,
            'icon' => 'sync',
        ]);

        // Notification channels

        Taxonomy::create([
            'group' => 'system',
            'type' => 'channel',
            'code' => 'push',
            'name' => 'Push',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'channel',
            'code' => 'mail',
            'name' => 'Correo',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'channel',
            'code' => 'sms',
            'name' => 'SMS',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'system',
            'type' => 'channel',
            'code' => 'slack',
            'name' => 'Slack',
            'active' => ACTIVE,
            'position' => 4,
        ]);


        // APP menu

        // Taxonomy::create([
        //     'group' => 'system',
        //     'type' => 'menu',
        //     'code' => 'home',
        //     'name' => 'Inicio',
        //     'active' => ACTIVE,
        //     'position' => 1,
        //     'parent_id' => $app->id,
        // ]);

        // Taxonomy::create([
        //     'group' => 'system',
        //     'type' => 'menu',
        //     'code' => 'announcements',
        //     'name' => 'Anuncios',
        //     'active' => ACTIVE,
        //     'position' => 2,
        //     'parent_id' => $app->id,
        // ]);

        

        // Taxonomy::create([
        //     'group' => 'system',
        //     'type' => 'menu',
        //     'code' => 'polls',
        //     'name' => 'Encuestas',
        //     'active' => ACTIVE,
        //     'position' => 6,
        //     'parent_id' => $app->id,
        // ]);

     

        // Taxonomy::create([
        //     'group' => 'system',
        //     'type' => 'menu',
        //     'code' => 'faq',
        //     'name' => 'Preguntas frecuentes',
        //     'active' => ACTIVE,
        //     'position' => 8,
        //     'parent_id' => $app->id,
        // ]);

        // Taxonomy::create([
        //     'group' => 'system',
        //     'type' => 'menu',
        //     'code' => 'help',
        //     'name' => 'Ayuda',
        //     'active' => ACTIVE,
        //     'position' => 9,
        //     'parent_id' => $app->id,
        // ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'type',
            'code' => 'employee',
            'icon' => 'person',
            'name' => 'Usuario',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        // Taxonomy::create([
        //     'group' => 'user',
        //     'type' => 'type',
        //     'code' => 'client',
        //     'icon' => 'folder_shared',
        //     'name' => 'Cliente',
        //     'active' => ACTIVE,
        //     'position' => 2,
        // ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'gender',
            'code' => 'male',
            'name' => 'Masculino',
            'icon' => 'male',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'gender',
            'code' => 'female',
            'name' => 'Femenino',
            'icon' => 'female',
            'active' => ACTIVE,
            'position' => 2,
        ]);


        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'coder',
            'name' => 'Programador',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'CEO',
            'name' => 'CEO',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'cto',
            'name' => 'CTO',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'analista',
            'name' => 'Analista',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'designer',
            'name' => 'Diseñador',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'qa',
            'name' => 'QA',
            'active' => ACTIVE,
            'position' => 6,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'job-position',
            'code' => 'designer',
            'name' => 'Diseñador',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'area',
            'code' => 'design',
            'name' => 'Diseño',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'area',
            'code' => 'develop',
            'name' => 'Desarrollo',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'area',
            'code' => 'marketing',
            'name' => 'Marketing',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'area',
            'code' => 'commercial',
            'name' => 'Comercial',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'area',
            'code' => 'support',
            'name' => 'Soporte',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'document-type',
            'code' => 'dni',
            'name' => 'DNI',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'user',
            'type' => 'document-type',
            'code' => 'visa',
            'name' => 'Carné de extranjería',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        // Taxonomy::create([
        //     'group' => 'user',
        //     'type' => 'country',
        //     'code' => 'peru',
        //     'name' => 'Perú',
        //     'alias' => 'pe',
        //     'active' => ACTIVE,
        //     'position' => 1,
        // ]);

        // Taxonomy::create([
        //     'group' => 'user',
        //     'type' => 'country',
        //     'code' => 'venezuela',
        //     'name' => 'Venezuela',
        //     'alias' => 've',
        //     'active' => ACTIVE,
        //     'position' => 2,
        // ]);

        // Taxonomy::create([
        //     'group' => 'user',
        //     'type' => 'country',
        //     'code' => 'argentina',
        //     'name' => 'Argentina',
        //     'alias' => 'ar',
        //     'active' => ACTIVE,
        //     'position' => 3,
        // ]);

        // Taxonomy::create([
        //     'group' => 'user',
        //     'type' => 'country',
        //     'code' => 'brasil',
        //     'name' => 'Brasil',
        //     'alias' => 'br',
        //     'active' => ACTIVE,
        //     'position' => 4,
        // ]);

    }
}
