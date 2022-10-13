<?php

namespace App\Http\Resources\Meeting;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingSearchAttendantsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {

        $idsCoHostCareers = Usuario::getCurrentHostsIds();
        $isCoHost = in_array($this->id, $idsCoHostCareers);

        return [
            'id' => $this->id,
            'dni' => $this->document,
            'nombre' => $this->nombre,
            // 'subworkspace_id' => $this->subworkspace_id,

            'carrera' => $carrera->nombre ?? 'Sin carrera',
            'isCoHost' => $isCoHost,

            'type_id' => '',

            'invitations' => $this->invitations ?? [],
            'invitations_count' => $this->invitations
                                    ? $this->invitations->count()
                                    : 0,
            'config' => $this->config ?? [
                'image' => '',
                'logo' => ''
             ],
        ];
    }
}
