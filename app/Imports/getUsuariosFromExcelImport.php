<?php

namespace App\Imports;

use App\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class getUsuariosFromExcelImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public $data = [];

    public function collection(Collection $excelData)
    {
        $usuarios_ids = collect();
        $filtered = $excelData->reject(function ($value, $key) {
            return $key == 0;
        });
        $filtered = $filtered->each(function ($value, $key) use($usuarios_ids) {
            $usuarios_ids->push(trim($value[0]));
        });
        
        $usuarios = Usuario::whereIn('dni', $usuarios_ids->all())
                        ->select('id', 'dni',  DB::raw(" CONCAT(dni,' - ',nombre, ', ', apellido_paterno, ' ', apellido_materno) as nombre"))
                        ->get();
        
        $diff = $usuarios_ids->diff($usuarios->pluck('dni')->all());
        $this->data = [
            'ok' => $usuarios->pluck('nombre', 'dni')->all(),
            'error' => $diff->all()
        ];
    }
    public function get_data()
    {
        return $this->data;
    }
}
