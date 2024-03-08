<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends BaseModel
{
    protected $fillable = [
        'stage_id',
        'title',
        'position',
        'active',
        'type_id',
        'model_id',
        'model_type',
        'activity_requirement_id',
        'percentage_ev'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id')->select(['id', 'code', 'name']);
    }

    public function projects()
    {
        return $this->morphMany(Project::class, 'model');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function requirement()
    {
        return $this->belongsTo(Activity::class, 'activity_requirement_id');
    }
}
