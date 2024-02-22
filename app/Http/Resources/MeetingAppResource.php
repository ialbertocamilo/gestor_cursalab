<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeetingAppResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $usuario_id = auth()->user()->id;
        $cohost = $this->attendants->where('type.code', 'cohost')->where('usuario_id', $usuario_id)->first();

        $attendant = $this->attendants->where('usuario_id', $usuario_id)->first();

        $was_present = ($attendant AND $attendant->first_login_at);
        $url = ($attendant AND $attendant->link) ? $attendant->link : $this->url;
        $isHostOrCohost = in_array($attendant?->type?->code, ['host', 'cohost']);

        return [
            'key' => $this->starts_at->format('Y-m-d'),

            'id' => $this->id,

            'title' => $this->name,
            'subtitle' => null,
            'url' => $url,
            'url_app_zoom' => str_replace("https://us02web.zoom.us/w/", "https://us02web.zoom.us/j/", $url),
            'url_start' => $isHostOrCohost ? $this->url_start : null,

            'time' => [
//                'title' => $this->getRangeTimeWithDuration(),
                'title' => $this->getRangeTime(),
                'duration' => $this->duration,
            ],

            'description' => $this->description,
            'session_code'=> 'live',
            'description_code'=>'Sesión virtual por zoom.',
            'date' => [
                'title' => get_title_date($this->starts_at),
                // 'title' => ucfirst($this->starts_at->formatLocalized('%A, %d de %B')),
                'value' => $this->starts_at->format('d/m/Y'),
                'starts_at' => $this->starts_at->format('Y-m-d H:i:s'),
                'finishes_at' => $this->finishes_at->format('Y-m-d H:i:s'),
                'timestamp' => (int) ($this->starts_at->timestamp . '000'),
                'timestamp_finish' => (int) ($this->finishes_at->timestamp . '000'),
                'is_today' => $this->starts_at->isToday(),
                'is_ontime' => $this->isOnTime(),
                'current_status' => $this->getCurrentDateStatus(),
            ],

            'host' => [
                'name' => $this->host->name,
                'usuario' => [
                    'id' => $this->host->id,
                    'name' => $this->host->fullname,
                ],
            ],

            'account' => [
                'name' => $this->account ? $this->account->name : '',
                'secret' => $this->account ? $this->account->secret : '',
                'key' => $this->account ? $this->account->key : '',
                'token' => $this->account ? $this->account->sdk_token : '',
                'service' => $this->account ? $this->account->service : '',
                // 'type' => $this->account->type,
            ],

            'password' => $this->password,
            'identifier' => $this->identifier,

            'type' => $this->type,
            'status' => $this->status,
            'code' => $this->buildPrefix(),

            'is_embed' => $this->embed,

            'assistance' => [
                'status' => $was_present,
                'text' => $was_present ? 'Asistí' : 'No asistí', // Ausente
            ],

            // 'current_usuario_is_host' => auth()->user()->id == $this->host->id,
            'current_usuario_is_host' => $usuario_id == $this->host->id,
            'current_usuario_is_cohost' => $cohost ? true : false,

            'attendants_count' => $this->attendants_count,
        ];
    }
}
