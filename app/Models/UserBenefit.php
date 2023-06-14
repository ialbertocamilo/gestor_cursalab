<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBenefit extends BaseModel
{
    protected $table = 'user_benefits';

    protected $fillable = [
        'user_id', 'benefit_id', 'status_id'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function benefits()
    {
        return $this->belongsTo(Benefit::class, 'benefit_id');
    }

}
