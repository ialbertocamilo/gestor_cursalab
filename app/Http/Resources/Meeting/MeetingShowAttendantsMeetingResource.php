<?php

namespace App\Http\Resources\Meeting;

use App\Services\MeetingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingShowAttendantsMeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {

//        info($usuario);
        $carrera = null; // $usuario->matricula_presente->carrera ?? null;
        // $idsCoHostCareers = MeetingService::getIdsCoHostCareers();
        // $isCoHost = in_array($carrera->id, $idsCoHostCareers);


        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario ? $this->usuario->id : '',
            'dni' => $this->usuario ? $this->usuario->document : '',
            'nombre' => $this->usuario ? $this->usuario->name.' '.$this->usuario->lastname.' '.$this->usuario->surname : '',
            'config' => $this->usuario ? $this->usuario->config : '',

            'carrera' => $carrera->nombre ?? 'No definido',
            'isCoHost' => $this->type->code === 'cohost',
            'type_id' => $this->type_id,

            'invitations' => $this->invitations,
            'invitations_count' => $this->invitations
                                    ? $this->invitations->count()
                                    : 0
            ,
            'link' => $this->link,

            'total_duration' => $this->total_duration ?? 0,
            'online' => $this->online,
            'present_at_first_call' => $this->present_at_first_call,
            'present_at_middle_call' => $this->present_at_middle_call,
            'present_at_last_call' => $this->present_at_last_call
        ];
    }
}
