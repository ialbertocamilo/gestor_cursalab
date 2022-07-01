<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistRpta extends Model
{
    protected $table = 'checklist_rptas';
    protected $fillable = [
        'entrenador_id',
        'alumno_id',
        'checklist_id',
        'calificacion',
        'porcentaje',
        'curso_id',
        'categoria_id'
    ];

    public function rpta_items()
    {
        return $this->hasMany(ChecklistRptaItem::class, 'checklist_rpta_id', 'id');
    }

    public function checklistRelation()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id', 'id');
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeEntrenador($q, $entrenador_id)
    {
        return $q->where('entrenador_id', $entrenador_id);
    }

    public function scopeAlumno($q, $alumno_id)
    {
        return $q->where('alumno_id', $alumno_id);
    }

    public function scopeChecklist($q, $checklist_id)
    {
        return $q->where('checklist_id', $checklist_id);
    }

    /*=================================================================================================================================== */

    public static function actualizarChecklistRpta(ChecklistRpta $checklistRpta) : void
    {-
        $cumple = 0;
        $checklistValidos = 0;
        $actividades = CheckListItem::where('checklist_id',$checklistRpta->checklist_id)->estado(1)->get();

        foreach ($actividades as $actividad) {
            if ($actividad->tipo === 'entrenador_usuario') {
                $checklistValidos++;
                $rpta_item = ChecklistRptaItem::where('checklist_item_id', $actividad->id)->where('checklist_rpta_id' ,$checklistRpta->id)->first();
                if($rpta_item && ($rpta_item->calificacion === 'Cumple' || $rpta_item->calificacion === 'No cumple') )  $cumple ++;
            }
        }

        $checklistRpta->porcentaje = number_format((($cumple / $checklistValidos) * 100), 2);
        $checklistRpta->save();
    }
}
