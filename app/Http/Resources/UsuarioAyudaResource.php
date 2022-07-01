<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioAyudaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $estados = config('constantes.soporte-estados');
        $colors = config('constantes.soporte-estados-colors');

        $view = $request->view == 'show' ? 'show' : 'search';

        $data = [
            'id' => $this->id,
            'estado' => $estados[$this->estado] ?? 'No definido',
            'status' => ['text' => $estados[$this->estado] ?? 'No definido', 'color' => $colors[$this->estado] ?? 'white'],
            'motivo' => clean_html($this->motivo, 60),
            'detalle' => $this->detalle,
            'dni' => $this->usuario->dni ?? '',
            'nombre' => $this->usuario->nombre ?? '',
            'image' => space_url($this->usuario->config->logo ?? ''),

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];

        if ($request->view == 'show')
        {
            $data['motivo'] = $this->motivo;
            $data['info_soporte'] = $this->info_soporte;
            $data['msg_to_user'] = $this->msg_to_user;
        }

        return $data;
    }
}
