<?php

namespace App\Imports;

use App\Models\CheckList;
use App\Models\CheckListItem;
use App\Models\Usuario;
use App\Models\EntrenadorUsuario;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ChecklistImport implements ToCollection
{
    public $data_ok;
    public $data_no_procesada;
    public $msg;

    public function collection(Collection $collection)
    {
        $data_ok = collect();
        $data_no_procesada = collect();

        // Remover cabeceras : TITULO, DESCRIPCION y LISTA DE ACTIVIDADES
        $collection = $collection->reject(function ($value, $key) {
            return $key == 0;
        });

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
            $checklist = CheckList::create(['titulo' => $titulo, 'descripcion' => $descripcion, 'estado' => 1]);
            foreach ($tempActividades->all() as $index => $actividad) {
                if (!is_null($actividad) && !empty($actividad)){
                    CheckListItem::create([
                        'checklist_id' => $checklist->id,
                        'actividad' => $actividad,
                        'tipo' => 'entrenador_usuario',
                        'estado' => 1
                    ]);
                }
            }
            if ($actividad_feedback){
                CheckListItem::create([
                    'checklist_id' => $checklist->id,
                    'actividad' => '¿El entrenador te acompañó, dio soporte y feedback durante el proceso de inducción? Responde en base a los procesos revisados.',
                    'tipo' => 'usuario_entrenador',
                    'estado' => 1
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
