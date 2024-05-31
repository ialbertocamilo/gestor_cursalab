<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegmentationCount extends Model
{
    protected $table = 'segmentation_count';

    protected $fillable = [
        'model_type',
        'model_id',
        'users_count'
    ];

}
