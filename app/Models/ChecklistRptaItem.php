<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistRptaItem extends Model
{
    protected $table = 'checklist_rptas_items';
    protected $fillable = [
        'checklist_rpta_id',
        'checklist_item_id',
        'calificacion',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function actividad()
    {
        return $this->belongsTo(CheckListItem::class, 'checklist_item_id');
    }

    public function checklist_rpta()
    {
        return $this->belongsTo(ChecklistRpta::class, 'checklist_rpta_id');
    }
}
