<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionalityConfig extends Model
{

    protected $table = 'functionality_config';

    protected $fillable = [
        'functionality_id',
        'config_id',
        'active'
    ];

    public $timestamps = false;


    protected $casts = [
        'active' => 'boolean'
    ];

    public function functionality()
    {
        return $this->belongsTo(Taxonomy::class, 'functionality_id');
    }

    public function configvalue()
    {
        return $this->belongsTo(Taxonomy::class, 'config_id');
    }
}
