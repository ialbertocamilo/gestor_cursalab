<?php

namespace App\Models\UCMigrationData;

use App\Models\Support\ExternalDatabase;
use App\Models\Support\ExternalLMSMigration;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Migration_3 extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);


    }
    // use HasFactory;

    public function migrateEvaluaciones()
    {
        $client_lms_data = ExternalLMSMigration::setMigrationData_1();

        ExternalDatabase::insertMigrationData_1($client_lms_data);
    }

    public function setEvaluacionesData(&$result, $db)
    {
        // $now = now()->format('Y-m-d H:i:s');
        $pruebas = $db->getTable('pruebas')->get();

        foreach ($pruebas as $prueba)
        {
            $result[] = [
                'external_id' => $prueba->id,

                'name' => $prueba->nombre,
                'lastname' => $prueba->apellido_paterno,
                'surname' => $prueba->apellido_materno,

                'email' => $prueba->email,
                'code' => $prueba->dni,

                'password' => Hash::make($prueba->dni),

                'active' => $prueba->estado,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $result['pruebas'] = array_chunk($result['users'], self::CHUNK_LENGTH, true);
    }
}
