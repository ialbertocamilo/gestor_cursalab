<?php

namespace App\Http\Resources\NotificacionPush;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionPushSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $total = $this->success + $this->failure;

        $porcentaje = $this->success == 0 ? 0 : $this->success / $total  * 100;

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'created_at' => $this->created_at->format('d/m/Y'),
            // 'fecha_envio' => $this->created_at->format('Y/m/d'),
            'users_reached' => $this->success . '/' . $total,

            'custom_notification_push_efectividad' => number_format($porcentaje),

            'estado' => $this->estado_envio,
            // 'users_reached' => '-',
        ];
    }
}
