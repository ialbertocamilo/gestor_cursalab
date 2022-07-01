<?php

namespace App\Models;

use App\Imports\ExamenImport;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    protected $fillable = [
    	'post_id', 'pregunta', 'rptas_json','ubicacion', 'estado', 'tipo_pregunta', 'created_at', 'updated_at', 'rpta_ok'
    ];

    public function posteo()
    {
        return $this->belongsTo(Posteo::class, 'post_id');
    }

    protected function importFromFile($data)
    {
        try {

            $model = (new ExamenImport);

            $model->posteo_id = $data['posteo_id'];

            $model->tipo_ev = $data['tipo_ev'];
            $model->import($data['excel']);

            if ($model->failures()->count())
            {
                request()->session()->flash('excel-errors', $model->failures());

                return ['status' => 'error', 'message' => 'Se encontraron algunos errores.'];
            }

        } catch (\Exception $e) {

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Registros ingresados correctamente.'];
    }

    protected function import($data)
    {
        try {

            $model = (new ExamenImport);

            $model->posteo_id = $data['posteo_id'];

            $model->tipo_ev = $data['tipo_ev'];
            $model->import($data['archivo']);

            if ($model->failures()->count())
            {
//                request()->session()->flash('excel-errors', $model->failures());

                return [
                    'msg' => 'Se encontraron algunos errores.',
                    'errors' => $model->failures()
                ];
            }

        } catch (\Exception $e) {

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Registros ingresados correctamente.'];
    }

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente',
        ];
    }
    /* AUDIT TAGS */
}
