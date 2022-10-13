<?php

namespace App\Models;

class ChecklistRptaItem extends BaseModel
{
    protected $table = 'checklist_answers_items';
    protected $fillable = [
        'checklist_answer_id',
        'checklist_item_id',
        'qualification',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function actividad()
    {
        return $this->belongsTo(CheckListItem::class, 'checklist_item_id');
    }

    public function checklist_rpta()
    {
        return $this->belongsTo(ChecklistRpta::class, 'checklist_answer_id');
    }
}
