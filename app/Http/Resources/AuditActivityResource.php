<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AuditActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
    	$name = $this->getRecordableName();
        
        $dataProcessed = $this->getModelProcessed();
        $modified_fields_count = $this->countModifiedFieldsFiltered();

    	$action = strtolower($this->action_name->name);
        $modified = implode(', ', $this->getModifiedLabels($dataProcessed['modified']));

       	$modified_fields_count = $modified_fields_count  . ($modified_fields_count > 1 ? ' valores' : ' valor');
        $gerund = $this->action_name->code == 'created' ? 'ingresando' : 'modificando';

        $text = "\"{$name}\" se {$action}";

        $text .= $this->action_name->code != 'deleted' ? " {$gerund} {$modified_fields_count} ({$modified})." : ".";

        $data = [
            'title' =>  $this->event_name->name . ' de ' . $this->getModelName(),
            'subtitle' => $this->created_at->format('g:i:s a'),
            'icon' => $this->event_name->icon ?? '',
            'text' => $text,
        ];

        return $data;
    }
}
