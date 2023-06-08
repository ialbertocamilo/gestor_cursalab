<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenefitProperty extends BaseModel
{

    protected $table = 'benefit_properties';

    protected $fillable = [
        'benefit_id',
        'type_id',
        'name',
        'value',
        'active'
    ];


    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function benefit()
    {
        return $this->belongsTo(Benefit::class, 'benefit_id');
    }
}
