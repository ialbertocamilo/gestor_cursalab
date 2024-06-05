<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistActivityAudit  extends BaseModel
{
    protected $table = 'checklist_activities_audit';
    protected $fillable = [
        'id',
        'checklist_audit_id',
        'identifier_request',
        'qualification_id',
        'photo',
        'checklist_id',
        'checklist_activity_id',
        'auditor_id',
        'date_audit',
        'historic_qualification',
        'comments'
    ];

    public function activity()
    {
        return $this->belongsTo(CheckListItem::class, 'checklist_activity_id');
    }
    public function qualification()
    {
        return $this->belongsTo(Taxonomy::class, 'qualification_id');
    }

    protected $casts = [
        'comments'=>'array',
        'photo'=>'array',
        'historic_qualification' => 'array',
        'date_audit' => 'timestamp',
    ];

    public function getDateAuditAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->format('d-m-Y');
    }

    protected function insertUpdateMassive($checklist_activities_audit,$type){
        $chunk_activities = array_chunk($checklist_activities_audit,10);
        foreach ($chunk_activities as $activities) {
            if($type == 'insert'){
                self::insert($activities);
            }
            if($type == 'update'){
                // dd($activities);
                batch()->update(new ChecklistActivityAudit, $activities, 'id');
            }
        }
    }
}
