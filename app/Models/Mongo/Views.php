<?php

namespace App\Models\Mongo;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class Views extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'views';
}
