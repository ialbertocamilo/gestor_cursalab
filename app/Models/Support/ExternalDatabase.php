<?php

namespace App\Models\Support;

use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use DB;

class ExternalDatabase extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

}
