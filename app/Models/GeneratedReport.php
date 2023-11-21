<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Altek\Accountant\Contracts\Recordable;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class GeneratedReport extends Model implements Recordable
{
    use HasFactory;
    use \Altek\Accountant\Recordable, \Altek\Eventually\Eventually;
    use HybridRelations;
    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'download_url', 'admin_id', 'workspace_id', 'filters', 'is_ready'
    ];
    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'generated_reports';

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public $defaultRelationships = ['admin_id' => 'admin'];

    protected $recordableEvents = ['downloaded'];

    protected function search($id)
    {
        return self::find($id);
    }
}
