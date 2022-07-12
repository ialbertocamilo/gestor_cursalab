<?php

namespace App\Models\Support;

use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use App\Models\Criterion;

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

}
