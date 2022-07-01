<?php

namespace App\Models;

use App\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;


class UsuariosDesactivos implements ToCollection
{
    private $q_cesados=0;
    private $q_errors = 0;
    public function collection(Collection $rows) {
        $errores = [];
        $total = count($rows);
        // PROCESAR CADA DNI
        for ($i=2; $i < $total; $i++) { 
            if(!empty($rows[$i][1])){
                $dni = $rows[$i][1];
                $user = Usuario::where('dni', $dni)->first();
                if($user){
                    $user->estado = 0;
                    $user->save();
                    $this->q_cesados++;
                }else{
                    //ERRORES
                    $error =[];
                    array_push($error,['tipo'=>'dni_error','celda'=>'D'.($i+1),'mensajes'=>'Dni no existe']);
                    $errores[]=[
                        'data' =>[
                            'dni' => $dni,
                        ],
                        'error' => $error,
                        'err_data_original'=>$rows[$i],
                    ];
                }
            }
        }
        $this->q_errors = count($errores);
        foreach ($errores as $err) {
            DB::table('err_masivos')->insert([
                'err_data_original' =>json_encode($err['err_data_original'],JSON_UNESCAPED_UNICODE),
                'err_data'=> json_encode($err['data']),
                'err_type' => json_encode($err['error']),
                'type_masivo' => 'cesados'
            ]);
        }
    }
    public function get_q_cesados()
    {
        return $this->q_cesados;
    }
    public function get_q_errors(){
        return $this->q_errors;
    }
}
