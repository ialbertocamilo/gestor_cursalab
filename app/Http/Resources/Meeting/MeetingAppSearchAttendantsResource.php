<?php

namespace App\Http\Resources\Meeting;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingAppSearchAttendantsResource extends JsonResource
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
        $workspaceIndex = auth()->user()->subworkspace->parent_id;
        $idsCoHostCareers = Usuario::getCurrentHosts(true, $workspaceIndex);
        # get hosts by workspace

        $isCoHost = in_array($this->id, $idsCoHostCareers);

        return [
            'id' => $this->id,
            'dni' => $this->document,
            'nombre' => $this->name.' '.$this->lastname.' '.$this->surname,
            // 'subworkspace_id' => $this->config_id,

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
