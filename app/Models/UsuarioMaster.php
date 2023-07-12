<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioMaster extends Model
{
    protected $connection = 'mysql_master';
    protected $table = 'master_usuarios';
    public $timestamps = false;
    protected $fillable = [
    	'dni','email','username','customer_id','created_at', 'updated_at', 'delete_at'
    ];


}
