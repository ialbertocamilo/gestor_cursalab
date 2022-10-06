<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioAyudaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
        $estados = config('constantes.soporte-estados');
        $colors = config('constantes.soporte-estados-colors');

        $data = [
            'id' => $this->id,
            'estado' => $estados[$this->status] ?? 'No definido',
            'status' => [
                'text' => $estados[$this->status] ?? 'No definido',
                'color' => $colors[$this->status] ?? 'default'
            ],
            'reason' => clean_html($this->reason, 60),
            'detail' => $this->detail,
            'dni' => $this->dni ?? '',
            'nombre' => $this->name ?? '',
            'image' => $this->workspace ? ($this->workspace->logo ? space_url($this->workspace->logo) : '') : '',
            'info_support' => $this->info_support ?? '',
            'msg_to_user' => $this->msg_to_user ?? '',

            'created_at' => $this->created_at?->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at?->format('d/m/Y g:i a'),
        ];

        if ($request->view == 'show') {
            $data['reason'] = $this->reason;
            $data['info_support'] = $this->info_support;
            $data['msg_to_user'] = $this->msg_to_user;
        }

        return $data;
    }
}
