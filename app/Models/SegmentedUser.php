<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegmentedUser extends Model
{
    protected $table = 'segmented_users';

    protected $fillable = [
        'model_type',
        'model_id',
        'user_id'
    ];
}
