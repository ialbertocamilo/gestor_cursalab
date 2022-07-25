<?php

namespace App\Models;

class Block extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'criterion_value_count',
    ];

}
