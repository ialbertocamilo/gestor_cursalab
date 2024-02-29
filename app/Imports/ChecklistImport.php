<?php

namespace App\Imports;

use App\Models\CheckList;
use App\Models\CheckListItem;
use App\Models\Usuario;
use App\Models\EntrenadorUsuario;
use App\Models\Taxonomy;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ChecklistImport implements ToCollection
{
    public $data_ok;
    public $data_no_procesada;
    public $msg;

    public function collection(Collection $collection)
    {
        $workspace = get_current_workspace();
        $data_ok = collect();
        $data_no_procesada = collect();
        $platform_training = Taxonomy::getFirstData('project', 'platform', 'training');

        // Remover cabeceras : TITULO, DESCRIPCION y LISTA DE ACTIVIDADES
        $collection = $collection->reject(function ($value, $key) {
            return $key == 0;
        });

        $type_checklist = Taxonomy::where('group', 'checklist')
                        ->where('type', 'type_checklist')
                        ->where('code', 'curso')
                        ->first();

        // Recorrer cada fila y crear el checklist
        foreach ($collection as $value) {
            $tempActividades = collect();
            $titulo = $value[0];
            $actividad_feedback = $value[2] === 'Si';
            $descripcion = $value[1];
            foreach ($value as $index => $actividad) {
                if ($index >= 3) $tempActividades->push($actividad);
            }
            // Crear CHECKLIST
            $checklist = CheckList::create([
                'title' => $titulo,
                'description' => $descripcion,
                'active' => 1,
                'workspace_id' => $workspace->id,
                'type_id' => !is_null($type_checklist) ? $type_checklist->id : null,
                'platform_id' => $platform_training?->id
            ]);
            foreach ($tempActividades->all() as $index => $actividad) {
                if (!is_null($actividad) && !empty($actividad)) {
                    $type = Taxonomy::where('group', 'checklist')
                        ->where('type', 'type')
                        ->where('code', 'trainer_user')
                        ->first();
                    CheckListItem::create([
                        'checklist_id' => $checklist->id,
                        'activity' => $actividad,
                        'type_id' => !is_null($type) ? $type->id : null,
                        'active' => 1
                    ]);
                }
            }
            if ($actividad_feedback) {
                $type = Taxonomy::where('group', 'checklist')
                    ->where('type', 'type')
                    ->where('code', 'user_trainer')
                    ->first();
                CheckListItem::create([
                    'checklist_id' => $checklist->id,
                    'activity' => '¿El entrenador te acompañó, dio soporte y feedback durante el proceso de inducción? Responde en base a los procesos revisados.',
                    'type_id' => !is_null($type) ? $type->id : null,
                    'active' => 1
                ]);
            }
        }
        //
        $this->msg = 'Excel procesado.';
        $this->data_no_procesada = $data_no_procesada;
        $this->data_ok = $data_ok;
    }

    public function get_data()
    {
        return [
            'msg' => $this->msg,
            'data_ok' => $this->data_ok,
            'data_no_procesada' => $this->data_no_procesada
        ];
    }
}
