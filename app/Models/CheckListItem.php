<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckListItem extends Model
{
    const TIPO_ITEM = [
        'entrenador_usuario', // Actividad que evalúa el entrenador a un usuario
        'usuario_entrenador' // Actividad de feedback por parte del usuario al entrenador en cada checklist
    ];

    protected $table = 'checklist_items';

    protected $fillable = [
        'checklist_id',
        'actividad',
        'tipo',
        'estado',
        'is_default',
        'posicion'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function checklist()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id');
    }

    protected function guardarActividadByID($data)
    {
        $response['error'] = false;
        $actividad  = CheckListItem::updateOrCreate(
            ['id' => $data['id']],
            ['actividad' => $data['actividad'], 'estado' => $data['estado'], 'tipo' => $data['tipo'], 'checklist_id' => $data['checklist_id']]
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

    /*=================================================================================================================================== */


}
