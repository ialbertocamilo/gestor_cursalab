<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignVotations extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'summoned_id', 'user_id'];

    public function summoned() {
        return $this->belongsTo(CampaignSummoneds::class, 'summoned_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
