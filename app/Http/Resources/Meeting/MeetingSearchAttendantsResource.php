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
        # get hosts by workspace
        $idsCoHostCareers = Usuario::getCurrentHostsIds();
        # get hosts by workspace

        $isCoHost = in_array($this->id, $idsCoHostCareers);

        # set type id
        $cohost_id = $request->cohost ? $request->cohost->id : NULL;
        $normal_id = $request->normal ? $request->normal->id : NULL;
        # set type id

        return [
            'id' => $this->id,
            'dni' => $this->document,
            'nombre' => $this->name.' '.$this->lastname.' '.$this->surname,
            // 'subworkspace_id' => $this->config_id,

            'carrera' => $carrera->nombre ?? 'Sin carrera',
            'isCoHost' => $isCoHost,

            'type_id' => $isCoHost ? $cohost_id : $normal_id,

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
