<?php

namespace App\Models;


class CheckListItem extends BaseModel
{
    const TIPO_ITEM = [
        'trainer_user', // Actividad que evalúa el entrenador a un usuario
        'user_trainer' // Actividad de feedback por parte del usuario al entrenador en cada checklist
    ];

    protected $table = 'checklist_items';

    protected $fillable = [
        'checklist_id',
        'activity',
        'type_id',
        'active',
        'position'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function checklist()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    protected function guardarActividadByID($data)
    {
        $response['error'] = false;
        $actividad  = CheckListItem::updateOrCreate(
            ['id' => $data['id']],
            [
                'activity' => $data['activity'],
                'active' => $data['active'],
                'type_id' => $data['type_id'],
                'checklist_id' => $data['checklist_id']
            ]
        );
        $response['actividad'] = $actividad;
        $response['msg']       = 'Actividad guardada';

        return $response;
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeEstado($q, $estado)
    {
        return $q->where('estado', $estado);
    }

    public function scopeActive($q, $active)
    {
        return $q->where('active', $active);
    }

    /*=================================================================================================================================== */
}
