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
        $modifiedFieldsCount = $this->countModifiedFieldsFiltered();

        $data = [
            'id' => $this->id,
            'user' => $this->user->name ?? 'Anónimo',
            'ip' => $this->ip_address,
            'url' => $this->url,
            'event' => $this->event_name->name ?? '',
            'action' => $this->action_name->name ?? '',
            'models' => $this->getModelName(),
            'modified_fields_count' => $modifiedFieldsCount  . ($modifiedFieldsCount > 1 ? ' valores' : ' valor'),
            'modified_fields' => $this->getModifiedFieldsFiltered(),
            'modified' => $dataProcessed['modified'],
            'total' => $dataProcessed['total'],
            'name' => $this->getRecordableName(),
            'pivot' => $this->pivot ? true : false,
            'pivot_data' => $this->pivot,
            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];

        return $data;
    }
}
