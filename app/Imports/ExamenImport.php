<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class ExamenImport implements WithHeadingRow, OnEachRow, WithValidation, WithChunkReading, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    public int|null $topic_id = NULL;
    public int|null $type_id = NULL;

    // Sum of questions score

    private int $totalScore = 0;

    // Max score per test

    public int $maxScore = 20;

    // Test is qualified or not

    public bool $isQualified;

    // Question types ids

    public $selectQuestionTypeId;
    public $writtenQuestionTypeId;

    public array $codes = [
        1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e',
        6 => 'f', 7 => 'g', 8 => 'h', 9 => 'i', 10 => 'j'
    ];

    public array $indexes = [
        'a' => 1,  'b' => 2,  'c' => 3,  'd' => 4,  'e' => 5,
        'f' => 6,  'g' => 7,  'h' => 8,  'i' => 9,  'j' => 10
    ];

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        // verificando el tipo de calificación
        // variable para saber si no supera el límite máximo antes de agregar

        if ($this->isQualified) {

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

            // Check if question is required

            $obligatorio = trim(strtolower($row['obligatorio']));
            $isRequired = ($obligatorio === 'sí' || $obligatorio === 'si');

            // Accumulate score

            if ($isRequired) {
                $this->totalScore += $row['puntaje'] ?? 0;

                if ($this->maxScore >= $this->totalScore) {
                    $score = $row['puntaje'] ?? 0;
                } else {
                    $score = 0;
                }

            } else {
                $score = 0;
            }

            Question::create([
                'topic_id' => $this->topic_id,
                'type_id' => $this->selectQuestionTypeId,
                'pregunta' => $row['pregunta'],
                'rptas_json' => $respuestas,
                'rpta_ok' => $this->indexes[$rpta_ok] ?? NULL,
                'active' => 1,
                'required' => $isRequired,
                'score' => $score
            ]);

        } else {

            Question::create([
                'topic_id' => $this->topic_id,
                'type_id' => $this->writtenQuestionTypeId,
                'pregunta' => $row['pregunta'],
                'rptas_json' => new \stdClass(),
                'rpta_ok' => '',
                'active' => 1,
                'required' => 0,
                'score' => null
            ]);
        }
    }

    public function  rules(): array
    {return [];
        if ($this->type_id == 'calificada') {
            return [
                'pregunta' => "required|distinct|unique:questions,pregunta,NULL,id,type_id,selecciona,post_id,{$this->topic_id}|max:20000",
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
        } else {
            $callback = function($attribute,$value,$fail){
                if(!empty($value)){
                    $fail('El campo '.$attribute.' debe ser vacío.');
                }
            };
            return [
                'pregunta' => "required|distinct|unique:preguntas,pregunta,NULL,id,tipo_pregunta,texto,post_id,{$this->topic_id}|max:20000",
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
