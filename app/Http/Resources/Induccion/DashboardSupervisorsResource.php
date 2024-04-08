<?php

namespace App\Http\Resources\Induccion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSupervisorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->processes->count()) {
            $status = [ 'text' => 'En curso', 'color' => 'green'];
        }
        else {
            $status = [ 'text' => 'Pendiente' ];'';
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'surname' => $this->surname,
            'fullname' => $this->fullname,
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace->name ?? 'No module',
            'status' => $status
        ];
    }
}
