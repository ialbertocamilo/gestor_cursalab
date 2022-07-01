<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula_criterio extends Model
{
    protected $table = 'matricula_criterio';

    protected $fillable = [
        'id', 'matricula_id', 'criterio_id', 'created_at', 'updated_at'
    ];

    public function curricula_criterio()
    {
        return $this->hasMany(Curricula_criterio::class, 'criterio_id', 'criterio_id');
    }

    public function criterio()
    {
        return $this->belongsTo(Criterio::class, 'criterio_id');
    }

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
}
