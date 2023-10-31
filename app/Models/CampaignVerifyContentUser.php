<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignVerifyContentUser extends Model
{
    use HasFactory;
    protected $table = 'campaign_verify_contents_user';
    protected $fillable = [ 'id','campaign_id','user_id','content_id','completed'];
    protected $casts = [
        'completed' => 'boolean',
    ];
}
