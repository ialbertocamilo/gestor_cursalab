<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmailSegment extends BaseModel
{
    protected $table = 'email_segments';

    protected $fillable = [
        'workspace_id',
        'benefit_id',
        'chunk',
        'users',
        'sent'
    ];

    protected $casts = [
        'sent' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];
}
