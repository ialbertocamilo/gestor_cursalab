<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBenefit extends BaseModel
{
    protected $table = 'user_benefits';

    protected $fillable = [
        'user_id', 'benefit_id', 'status_id', 'type_id', 'fecha_encuesta', 'fecha_confirmado', 'fecha_registro'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function benefits()
    {
        return $this->belongsTo(Benefit::class, 'benefit_id');
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

}
