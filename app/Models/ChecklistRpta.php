<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistRpta extends Model
{
    protected $table = 'checklist_answers';
    protected $fillable = [
        'coach_id',
        'student_id',
        'checklist_id',
        // 'calificacion',
        'percent',
        'course_id',
        'school_id'
    ];

    public function rpta_items()
    {
        return $this->hasMany(ChecklistRptaItem::class, 'checklist_answer_id', 'id');
    }

    public function checklistRelation()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id', 'id');
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeEntrenador($q, $entrenador_id)
    {
        return $q->where('coach_id', $entrenador_id);
    }

    public function scopeAlumno($q, $alumno_id)
    {
        return $q->where('student_id', $alumno_id);
    }

    public function scopeChecklist($q, $checklist_id)
    {
        return $q->where('checklist_id', $checklist_id);
    }

    /*=================================================================================================================================== */

    public static function actualizarChecklistRpta(ChecklistRpta $checklistRpta): void
    {
        $cumple = 0;
        $checklistValidos = 0;
        $actividades = CheckListItem::where('checklist_id', $checklistRpta->checklist_id)->active(1)->get();

        foreach ($actividades as $actividad) {
            $tax_trainer_user = Taxonomy::where('group', 'checklist')
                ->where('type', 'type')
                ->where('code', 'trainer_user')
                ->first();
            if ($actividad->type_id == $tax_trainer_user->id) {
                $checklistValidos++;
                $rpta_item = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_answer_id', $checklistRpta->id)->first();
                if ($rpta_item && ($rpta_item->qualification === 'Cumple' || $rpta_item->qualification === 'No cumple'))  $cumple++;
            }
        }

        $checklistRpta->percent = number_format((($cumple / $checklistValidos) * 100), 2);
        $checklistRpta->save();
    }
}
