<?php

namespace App\Http\Resources\Induccion;

use App\Models\Process;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SupervisorProcessesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {
        $user = Auth::user();
        // if($data) {
            $process = Process::where('id',$this->id)->first();
            $participants = Process::getProcessAssistantsList(process:$process, by_supervisor:$user, is_paginated:false);
            $total_participants = $participants->count() ?? 0;
            $participants_finished = 0;
            $status_finished = Taxonomy::getFirstData('user-process', 'status', 'finished')->id;
            foreach ($participants as $student) {
                $student_finished = $student->summary_process()->where('process_id', $this->id)->where('status_id', $status_finished)->first();
                if($student_finished) {
                    $participants_finished += 1;
                }
            }
        // }
         return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'title' => $this->title,
            'active' => $this->active ?? false,

            'finishes_at' => $this->finishes_at ? date('d-m-Y', strtotime($this->finishes_at)) : null,
            'starts_at' => $this->starts_at ? date('d-m-Y', strtotime($this->starts_at)) : null,
            'participants' => $total_participants,
            'percentage' => $participants_finished,
        ];
    }
}
