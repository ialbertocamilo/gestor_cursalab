<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockChild extends Model
{
    protected $table = 'blocks_children';

    // protected $with = ['segment'];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function child()
    {
        return $this->belongsTo(Block::class, 'block_child_id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'blocks_courses', 'block_id');
    }

    // public function criterion_values()
    // {
    //     return $this->belongsToMany(CriterionValue::class);
    // }
}
