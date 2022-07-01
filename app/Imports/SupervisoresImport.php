<?php

namespace App\Imports;

use App\Models\Usuario;
use App\Models\Criterio;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class SupervisoresImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public $data_ok;
    public $data_no_procesada;
    public $msg;

    public function collection(Collection $collection)
    {
        $data_ok = collect();
        $data_no_procesada = collect();

        // Remover cabeceras
        $collection = $collection->reject(function ($value, $key) {
            return $key == 0;
        });

        // Recorrer cada fila y crear el Supervisor
        foreach ($collection as $value) {
            $temp = [
                'dni' => $value[0],
                'grupo' => $value[1],
            ];
            info($temp);
            $usuario = Usuario::where('dni',trim($temp['dni']))->select('id','config_id')->first();
            if($usuario){
                $grupo = Criterio::where('config_id',$usuario->config_id)->where('valor',strtoupper(trim($temp['grupo'])))->select('id')->first();
                if($grupo){
                    DB::table('supervisores')->updateOrInsert(
                        ['usuario_id' => $usuario->id],
                        [
                            'usuario_id' => $usuario->id,
                            'criterio_id' => $grupo->id
                       ]
                    );
                }else{
                    $temp['msg'] = 'Grupo no encontrado';
                    $data_no_procesada->push($temp);
                }
            }else{
                $temp['msg'] = 'Usuario no encontrado';
                $data_no_procesada->push($temp);
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
