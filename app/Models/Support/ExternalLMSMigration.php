<?php

namespace App\Models\Support;

use App\Models\Taxonomy;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Model;

use DB;
use Illuminate\Support\Facades\Hash;

class ExternalLMSMigration extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function setMigrationData_1()
    {
        $db = self::connect();
        $client_LMS_data = [
            'users' => [], 'carreras' => [], 'ciclos' => [], 'grupos' => [],
            'boticas' => [], 'modulos' => []
        ];

        // TODO: Migrate users / usuarios
        $this->setUsersData($client_LMS_data, $db);
        // TODO: Migrate carreras
        // TODO: Migrate ciclos
        // TODO: Migrate grupos
        // TODO: Migrate boticas
        // TODO: Migrate modulos

        return $client_LMS_data;
    }

    public function setUsersData(&$result, $db)
    {
        $now = now()->format('Y-m-d H:i:s');
        $temp['users'] = $db->getTable('usuarios')
            ->select(
                'id', 'nombre', 'apellido_paterno', 'apellido_materno',
                'email', 'dni', 'estado',
                'created_at', 'updated_at'
            )
            ->get();

        foreach ($temp['users'] as $user) {
            $result['users'][] = [
                'external_id' => $user->id,

                'name' => $user->nombre,
                'lastname' => $user->apellido_paterno,
                'surname' => $user->apellido_materno,

                'email' => $user->email,
                'code' => $user->dni,

                'password' => Hash::make($user->dni),

                'active' => $user->estado,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $result['users'] = array_chunk($result['users'], self::CHUNK_LENGTH, true);
    }


}
