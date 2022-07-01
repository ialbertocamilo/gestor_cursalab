<?php

namespace App\Http\Resources\Meeting;

use App\Services\MeetingService;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingSearchAttendantsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        $this->load('matricula_presente.carrera');
        $carrera = $this->matricula_presente->carrera ?? null;
        $idsCoHostCareers = MeetingService::getIdsCoHostCareers();
        $isCoHost = in_array($carrera->id ?? null, $idsCoHostCareers);

        $cohost_id = $request->cohost ? $request->cohost->id : NULL;
        $normal_id = $request->normal ? $request->normal->id : NULL;

        return [
            'id' => $this->id,
            'dni' => $this->dni,
            'nombre' => $this->nombre,

            'carrera' => $carrera->nombre ?? 'Sin carrera',
            'isCoHost' => $isCoHost,

            'type_id' => $isCoHost ? $cohost_id : $normal_id,

            'invitations' => $this->invitations,
            'invitations_count' => $this->invitations ? $this->invitations->count() : 0,

            'config' => $this->config,
        ];
    }
}
