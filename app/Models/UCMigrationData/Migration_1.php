<?php

namespace App\Models\UCMigrationData;

use App\Models\Support\ExternalDatabase;
use App\Models\Support\ExternalLMSMigration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Migration_1 extends Model
{
    use HasFactory;

    public function migrateData1()
    {
        $client_lms_data = ExternalLMSMigration::setMigrationData_1();

        ExternalDatabase::insertMigrationData_1($client_lms_data);
    }
}
