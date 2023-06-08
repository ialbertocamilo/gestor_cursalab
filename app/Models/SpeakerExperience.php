<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpeakerExperience extends BaseModel
{
    protected $table = 'speaker_experience';

    protected $fillable = [
        'speaker_id',
        'company',
        'occupation',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function speaker()
    {
        return $this->belongsTo(Speaker::class, 'speaker_id', 'id');
    }
}
