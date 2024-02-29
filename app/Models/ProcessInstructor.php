<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProcessInstructor extends BaseModel
{
    protected $table = 'process_instructors';

    protected $fillable = [
        'user_id',
        'process_id',
        'type'
    ];

    public function relations()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function instructors()
    {
        $tax_supervice= Taxonomy::getFirstData('segment', 'code', 'user-supervise');
        return $this->relations()->where('code', $tax_supervice);
    }

    public function processes()
    {
        return $this->hasMany(Process::class, 'process_id', 'id');
    }
}
