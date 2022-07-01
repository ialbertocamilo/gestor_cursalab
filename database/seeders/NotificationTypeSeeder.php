<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $models = Taxonomy::where('group', 'system')->where('type', 'model')->get();
        $types = Taxonomy::where('group', 'notification')->where('type', 'type')->get();

        // Editor

        // NotificationType::create([
        //     'name' => 'Tarea creada',
        //     'active' => ACTIVE,
        //     'description' => 'Tarea creada en la plataforma. Se notifica a todo el área.',
        //     'model_id' => $models->where('code', 'task')->first()->id,
        //     'type_id' => $types->where('code', 'event')->first()->id,
        // ]);

        // NotificationType::create([
        //     'name' => 'Tarea asignada',
        //     'active' => ACTIVE,
        //     'description' => 'Tarea asignada en la plataforma. Se notifica solo al encargado de ver la tarea.',
        //     'model_id' => $models->where('code', 'task')->first()->id,
        //     'type_id' => $types->where('code', 'event')->first()->id,
        // ]);

        // NotificationType::create([
        //     'name' => 'Tarea - Fecha de vencimiento cercana',
        //     'active' => ACTIVE,
        //     'description' => 'Notificar por cada fecha de vencimiento cercana',
        //     'model_id' => $models->where('code', 'task')->first()->id,
        //     'type_id' => $types->where('code', 'alarm')->first()->id,
        // ]);

        NotificationType::create([
            'name' => 'Registro en tablas maestras',
            'active' => ACTIVE,
            'description' => 'Notificar por cada cambio realizado en las tablas maestras',
            'model_id' => $models->where('code', 'audit')->first()->id,
            'type_id' => $types->where('code', 'event')->first()->id,
        ]);
        
        // Usuario

        NotificationType::create([
            'name' => 'Contenido aprobado',
            'active' => ACTIVE,
            'description' => 'Notificar por cada contenido aprobado',
            'model_id' => $models->where('code', 'user')->first()->id,
            'type_id' => $types->where('code', 'event')->first()->id,
        ]);

        NotificationType::create([
            'name' => 'Contenido reprobado',
            'active' => ACTIVE,
            'description' => 'Notificar por cada contenido reprobado',
            'model_id' => $models->where('code', 'user')->first()->id,
            'type_id' => $types->where('code', 'event')->first()->id,
        ]);

        // Cliente

        // NotificationType::create([
        //     'name' => 'Contenido publicado',
        //     'active' => ACTIVE,
        //     'description' => 'Notificar por cada contenido publicado',
        //     'model_id' => $models->where('code', 'client')->first()->id,
        //     'type_id' => $types->where('code', 'event')->first()->id,
        // ]);

    }
}
