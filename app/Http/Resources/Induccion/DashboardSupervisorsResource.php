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
         return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'surname' => $this->surname,
            'fullname' => $this->fullname,
            'department' => 'department',
            'job' => 'job',
            'status' => 'status'
        ];
    }
}
