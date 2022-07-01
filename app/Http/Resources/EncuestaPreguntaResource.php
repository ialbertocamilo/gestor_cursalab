<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EncuestaPreguntaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $tipos = config('constantes.tipopreg');
        // $tipos = config('data.tipo-preguntas');

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            // 'tipo_pregunta' => $this->tipo_pregunta,
            'cantidad' => $this->getOptionsCount(),
            'tipo_pregunta' => $tipos[$this->tipo_pregunta] ?? '',
            'active' => $this->estado ? true : false,

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->created_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }

    public function getOptionsCount()
    {
        if (in_array($this->tipo_pregunta, ['texto', 'califica']))
            return "-";

        $options = json_decode($this->opciones, true);

        return count($options);
    }
}
