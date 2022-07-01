<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class ErrorX extends Model
{
    use HasFactory;

    public $connection = 'mongodb';
    protected $collection = 'errors';
    protected $guarded = [];


    public function scopeById($query, $value)
    {
        return $query->where('_id', 'like', "%$value%");
    }

    public function scopeByMessage($query, $value)
    {
        return $query->where('message', 'like', "%$value%");
    }

    public function scopeByStackTrace($query, $value)
    {
        return $query->where('stack_trace', 'like', "%$value%");
    }
}
