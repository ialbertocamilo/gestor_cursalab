<?php

namespace App\Imports;

use App\Models\CriterionValue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CriterionValueImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public $data_ok;
    public $data_no_procesada;
    public $msg;
    public $criterion_id;

    public function collection(Collection $collection)
    {
        $data_ok = collect();
        $data_no_procesada = collect();
        // Remover cabeceras : CRITERIO
        $collection = $collection->reject(function ($value, $key) {
            return $key == 0;
        });

        $current_workspace = get_current_workspace();
        $workspace_id = $current_workspace?->id;

        foreach ($collection as $row) {

            $value_text = trim($row[0]);

            if (!is_null($value_text) && !empty($value_text)){

                $value = CriterionValue::with('workspaces:id,name')
                            ->where('value_text', $value_text)
                            ->where('criterion_id', $this->criterion_id)->first();

                if($value){
                    
                    $workspace = $value->workspaces->where('id', $workspace_id)->first();

                    if ($workspace) {

                        $data_no_procesada->push([
                            'criterio'=> $value_text,
                            'msg'=>'Valor de criterio ya existente en el workspace.'
                        ]);

                    } else {

                        $value->workspaces()->syncWithoutDetaching([$workspace_id]);
                    }

                }else{

                    $value = CriterionValue::create([
                        'criterion_id' => $this->criterion_id,
                        'value_text' => $value_text,
                        'active' => 1,
                    ]);

                    $value->workspaces()->syncWithoutDetaching([$workspace_id]);
                }
            }
        }
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
