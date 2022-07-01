<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posteo_perfil extends Model
{
    protected $table = 'posteo_perfil';

    protected $fillable = [
        'posteo_id', 'perfile_id'
    ];
    
    public $timestamps = false;

    public function posteo()
    {
        return $this->belongsTo(Posteo::class, 'posteo_id');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

}