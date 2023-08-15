<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectUserSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if(count($this->project_resources)>0){
            foreach ($this->project_resources as $project_resource) {
                $file = $project_resource->path_file;
                $project_resource->full_path_file =  str_contains($file,'http') ? $file : env('DO_URL') . "/" .$file ;
            }
        }

        return [
            'subworkspace'=>$this->subworkspace->name,
            'name'=>$this->name,
            'lastname'=>$this->lastname.' '.$this->sourname,
            'document'=>$this->document,
            'status_project' => isset($this->project[0]->status) ? $this->project[0]->status->name : 'Pendiente',
            'status_project_id' => isset($this->project[0]->status) ? $this->project[0]->status->id : null,
            'project_user_id' => isset($this->project[0]->id) ? $this->project[0]->id : null,
            'msg_to_user' => isset($this->project[0]->msg_to_user) ? $this->project[0]->msg_to_user : null,
            'resources_user' => $this->project_resources,
            'has_resource' => count($this->project_resources)>0
        ]; 
    }
}
