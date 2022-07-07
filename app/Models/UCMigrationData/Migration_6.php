<?php

namespace App\Models\UCMigrationData;

use App\Models\Support\ExternalDatabase6;
use App\Models\Support\ExternalLMSMigration6;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Migration_6 extends Model
{
    use HasFactory;

    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function migrateData()
    {
        $client_lms_data = ExternalLMSMigration6::setMigrationData();
        ExternalDatabase6::insertMigrationData($client_lms_data);
    }
}
