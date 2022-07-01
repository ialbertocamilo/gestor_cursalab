<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosteoCompas extends Model
{
    protected $table = 'posteos_compatibles';
    // protected $fillable = [
    // 	'config_id',
    // ];

    public function posteo()
    {
        return $this->belongsTo(Posteo::class, 'tema_id');
    }
    public function posteo_compa()
    {
        return $this->belongsTo(Posteo::class, 'tema_compa_id');
    }
}
