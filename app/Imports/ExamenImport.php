<?php

namespace App\Imports;

use App\Models\Pregunta;

use Maatwebsite\Excel\Row;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;

use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ExamenImport implements WithHeadingRow, OnEachRow, WithValidation, WithChunkReading, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    public $posteo_id = NULL;
    public $tipo_ev = NULL;

    //se usa para llevar la cuenta del los puntajes de las preguntas obligatorias
    public $total_puntaje = 0;
    //saber el tipo de evaluación
    public $puntaje_max = 0;

    public $codes = [
        1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e',
        6 => 'f', 7 => 'g', 8 => 'h', 9 => 'i', 10 => 'j'
    ];

    public $indexes = [
        'a' => 1,  'b' => 2,  'c' => 3,  'd' => 4,  'e' => 5,
        'f' => 6,  'g' => 7,  'h' => 8,  'i' => 9,  'j' => 10
    ];

    public function onRow(Row $row)
    {
        $row = $row->toArray();
        //verificando el tipo de calificación
        //variable para saber si no supera el limete máximo antes de agregar
        if($this->tipo_ev=='calificada'){
            $respuestas = [];
            $rpta_ok = strtolower(trim($row['respuesta_correcta']));
            $n_rpta_ok = $this->indexes[$rpta_ok] ?? NULL;

            foreach ($this->codes as $position => $code) {
                if (isset($row[$code]) and !is_null($row[$code])) {
                    $row[$code] = $row[$code] === true ? 'Verdadero' : $row[$code];
                    $row[$code] = $row[$code] === false ? 'Falso' : $row[$code];
                    $correcta = $n_rpta_ok == $position;
                    $respuestas[$position] = $row[$code];
                }
            }
            $pregunta = Pregunta::create([
                'post_id' => $this->posteo_id,
                'tipo_pregunta' => 'selecciona',
                'pregunta' => $row['pregunta'],
                'rptas_json' => json_encode($respuestas,JSON_UNESCAPED_UNICODE),
                'rpta_ok' => $this->indexes[$rpta_ok] ?? NULL,
                'ubicacion' => 'despues',
                'estado' => 1,
            ]);
        }else{
            $pregunta = Pregunta::create([
                'post_id' => $this->posteo_id,
                'tipo_pregunta' => 'texto',
                'pregunta' => $row['pregunta'],
                'rptas_json' => null,
                'rpta_ok' => NULL,
                'ubicacion' => 'despues',
                'estado' => 1,
                'obligatorio' => 0,
                'puntaje' => null,
            ]);
        }
    }

    public function  rules(): array
    {
        if($this->tipo_ev=='calificada'){
            return [
                'pregunta' => "required|distinct|unique:preguntas,pregunta,NULL,id,tipo_pregunta,selecciona,post_id,{$this->posteo_id}|max:20000",
                // 'pregunta' => ["required","distinct","unique:preguntas","pregunta","NULL","id","post_id","{$this->posteo_id}","max:20000"],
                'respuesta_correcta' => 'required|in:A,B,C,D,E,F,G,H,I,J,a,b,c,d,e,f,g,h,i,j',

                'a' => 'required|max:5000',
                'b' => 'required|max:5000',

                'c' => 'nullable|required_if:respuesta_correcta,c,C|max:5000',
                'd' => 'nullable|required_if:respuesta_correcta,d,D|max:5000',
                'e' => 'nullable|required_if:respuesta_correcta,e,E|max:5000',
                'f' => 'nullable|required_if:respuesta_correcta,f,F|max:5000',
                'g' => 'nullable|required_if:respuesta_correcta,g,G|max:5000',
                'h' => 'nullable|required_if:respuesta_correcta,h,H|max:5000',
                'i' => 'nullable|required_if:respuesta_correcta,i,I|max:5000',
                'j' => 'nullable|required_if:respuesta_correcta,j,J|max:5000',
            ];
        }else{
            $callback = function($attribute,$value,$fail){
                if(!empty($value)){
                    $fail('El campo '.$attribute.' debe ser vacío.');
                }
            };
            return [
                'pregunta' => "required|distinct|unique:preguntas,pregunta,NULL,id,tipo_pregunta,texto,post_id,{$this->posteo_id}|max:20000",
                'a' => [$callback],
                'b' => [$callback],
                'c' => [$callback],
                'd' => [$callback],
                'e' => [$callback],
                'f' => [$callback],
                'g' => [$callback],
                'h' => [$callback],
                'i' => [$callback],
                'j' => [$callback],
            ];
        }
    }

    public function customValidationAttributes()
    {
        return ['respuesta_correcta' => 'respuesta correcta'];
    }

    public function customValidationMessages()
    {
        return [
            '*.required_if' => 'La opción ":attribute" es requerida si es marcada como respuesta correcta.',
            '*.required' => 'La opción ":attribute" es necesaria en una evaluación calificada.',
            '*.unique' => 'Esta pregunta ya ha sido asignada a la evaluación',
        ];
    }

    public function chunkSize(): int
    {
        return 10;
    }

}
