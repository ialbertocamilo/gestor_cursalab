<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curricula_criterio extends Model
{
    protected $table = 'curricula_criterio';

    protected $fillable = [
        'id', 'curricula_id', 'criterio_id', 'created_at', 'updated_at'
    ];

    public function curricula()
    {
        return $this->belongsTo(Curricula::class, 'curricula_id');
    }

    public function criterio()
    {
        return $this->belongsTo(Criterio::class, 'criterio_id');
    }
}