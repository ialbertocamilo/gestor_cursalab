<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagRelationship extends Model
{
    protected $fillable = [
    	'tag_id', 'element_type', 'element_id'
    ];

    public function relationships()
    {
        return $this->hasMany(Curricula::class, 'ciclo_id');
    }


    public function posteo()
    {
        return $this->belongsTo(Posteo::class, 'element_id')->where('element_type','posteo');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'element_id')->where('element_type','curso');
    }

    public function escuela()
    {
        return $this->belongsTo(Categoria::class, 'element_id')->where('element_type','escuela');
    }
}
