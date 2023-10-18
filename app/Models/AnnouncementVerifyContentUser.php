<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementVerifyContentUser extends Model
{
    use HasFactory;
    protected $table = 'announcement_verify_content_user';
    protected $fillable = [ 'id','announcement_id','user_id','content_id','completed'];
    protected $casts = [
        'completed' => 'boolean',
    ];
}
