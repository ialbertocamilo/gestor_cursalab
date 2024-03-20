<?php

namespace App\Http\Resources\Induccion;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class SupervisorProcessesStudentsResource extends JsonResource
{
    private static $data;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = self::$data;
        $count_absences = $data ? ($this->resource->summary_process()->where('process_id', $data['process_id'])->first()?->absences ?? 0) : null;
        $absences = null;
        if($data && $data['count_absences']){
            if($data['limit_absences'])
                $absences = $count_absences.'/'.$data['absences'];
            else
                $absences = $count_absences;
        }

        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace?->name ?? 'No module',
            'active' => !!$this->active,
            'absences' => $absences,
            'percentage' => 0
        ];
    }

    public static function customCollection($resource, $data): AnonymousResourceCollection
    {
      self::$data = $data;
      return parent::collection($resource);
    }
}
