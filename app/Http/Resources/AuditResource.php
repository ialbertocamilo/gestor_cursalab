<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AuditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dataProcessed = $this->getModelProcessed();
        $modified_fields_count = $this->countModifiedFieldsFiltered();

        // $cover_user = $this->user->getFirstMediaUrl('avatar', 'thumb');

        $data = [
            // 'avatar' => empty($cover_user) ? 'default_user.png' : $cover_user,
            'id' => $this->id,
            'user' => $this->user->name ?? 'Anónimo',
            'ip' => $this->ip_address,
            // 'url' => 'URL',
            'url' => $this->url,
            'event' => $this->event_name->name ?? '',
            'action' => $this->action_name->name ?? '',
            'model' => $this->getModelName(),
            'modified_fields_count' => $modified_fields_count  . ($modified_fields_count > 1 ? ' valores' : ' valor'),
            'modified_fields' => $this->getModifiedFieldsFiltered(),
            'modified' => $dataProcessed['modified'],
            'total' => $dataProcessed['total'],
            'name' => $this->getRecordableName(),
            // 'last' => '',
            'pivot' => $this->pivot ? true : false,

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];

        return $data;
    }
}
