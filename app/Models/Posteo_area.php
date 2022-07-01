<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posteo_area extends Model
{
    protected $table = 'posteo_area';

    protected $fillable = [
        'posteo_id', 'area_id'
    ];
    
    public $timestamps = false;

    public function posteo()
    {
        return $this->belongsTo(Posteo::class, 'posteo_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}