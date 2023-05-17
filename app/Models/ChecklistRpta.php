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
        'flag_congrats',
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
        return (is_array($alumno_id)) ? $q->whereIn('student_id', $alumno_id) :  $q->where('student_id', $alumno_id);
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

        $tax_trainer_user = Taxonomy::where('group', 'checklist')
            ->where('type', 'type')
            ->where('code', 'trainer_user')
            ->first();
        foreach ($actividades as $actividad) {
            if ($actividad->type_id == $tax_trainer_user->id) {
                $checklistValidos++;
                $rpta_item = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_answer_id', $checklistRpta->id)->first();
                if ($rpta_item && ($rpta_item->qualification === 'Cumple' || $rpta_item->qualification === 'No cumple'))  $cumple++;
            }
        }
      

        $percent = ($checklistValidos > 0) ? (($cumple / $checklistValidos) * 100) : 0;
        $percent = round(($percent > 100) ? 100 : $percent); // maximo porcentaje = 100
        $checklistRpta->percent = $percent;
        $checklistRpta->update();
    }
}
