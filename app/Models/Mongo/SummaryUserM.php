<?php

namespace App\Models\Mongo;

use Carbon\Carbon;
use App\Models\Topic;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class ViewsM extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'summary_user';
}
?>