<?php

namespace App\Models;

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
        'date_audit'
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
        'date_audit' => 'timestamp',
    ];

    public function getDateAuditAttribute($value)
    {
        $date = Carbon::parse($value);
        return $date->format('d-m-Y');
    }
}
