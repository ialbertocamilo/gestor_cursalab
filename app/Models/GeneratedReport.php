<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'download_url', 'admin_id', 'workspace_id', 'filters', 'is_ready'
    ];

    protected $table = 'generated_reports';

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
