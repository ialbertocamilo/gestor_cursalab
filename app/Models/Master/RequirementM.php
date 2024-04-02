<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequirementM extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $connection = 'mysql_master';
    protected $table = 'requirements';
}
