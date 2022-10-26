<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAction extends BaseModel
{
    protected $table = 'user_actions';

    protected $fillable = [
        'user_id', 'type_id', 'model_type', 'model_id', 'score'
    ];
}
