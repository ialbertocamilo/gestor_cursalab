<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuantity extends Model
{
    protected $table = 'users_quantity';
    protected $fillable = ['workspace_id','subworkspace_id', 'total', 'active', 'inactive', 'date'];


}
