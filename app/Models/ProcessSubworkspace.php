<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessSubworkspace extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'process_subworkspace';
    protected $fillable = [
    	'process_id',
        'subworkspace_id',
        'position',
    ];
}
