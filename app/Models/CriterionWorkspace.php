<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriterionWorkspace extends Model
{
    use HasFactory;

    protected $table = 'criterion_workspace';

    public function criterion()
    {
        return $this->belongsTo(Criterion::class, 'criterion_id');
    }
}
