<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneratedReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'report_type' => $this->report_type,
            'download_url' => $this->download_url,
            'admin' => $this->admin,
            'filters' => json_decode($this->filters),
            'filters_descriptions' => json_decode($this->filters_descriptions),
            'is_ready' => $this->is_ready,
            'is_processing' => $this->is_processing,
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
