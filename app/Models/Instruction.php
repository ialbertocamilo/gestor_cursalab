<?php

namespace App\Models;

class Instruction extends BaseModel
{
    protected $fillable = [
        'process_id',
        'description',
        'position'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }
}
