<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSubworkspace extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'school_subworkspace';
    protected $fillable = [
    	'school_id',
        'subworkspace_id',
        'position',
    ];
}
