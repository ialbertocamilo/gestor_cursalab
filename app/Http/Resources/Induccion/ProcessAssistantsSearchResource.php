<?php

namespace App\Http\Resources\Induccion;

use App\Models\Activity;
use App\Models\Stage;
use App\Models\Taxonomy;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessAssistantsSearchResource extends JsonResource
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
        $user_summary_process = $data ? ($this->resource->summary_process()->where('process_id', $data['process_id'])->first()) : null;

        $user_absences = null;
        $user_activities_progress = null;
        $user_activities_progressbar = null;
        $user_activities_total = null;

        if($data)
        {
            if($data['count_absences']) {
                $count_absences = $user_summary_process?->absences ?? 0;
                if($data['limit_absences']) {
                    $user_absences = $count_absences;
                }
                else {
                    $absences = $data['absences'] ?? 0;
                    $user_absences = $count_absences.'/'.$absences;
                }
            }
            else {
                $user_absences = null;
            }

            
            $status_finished = Taxonomy::getFirstData('user-activity', 'status', 'finished')->id;
            $user_activities = $this->resource->summary_process_activities()
                                ->whereHas('activity', function($a) use ($data) {
                                    $stages_ids = Stage::select('id')->where('process_id', $data['process_id'])->pluck('id')->toArray();
                                    $a->whereIn('stage_id', $stages_ids);
                                })
                                ->where('status_id', $status_finished)
                                ->count();
            $total_activities = Activity::select('id')->whereIn('stage_id', Stage::select('id')->where('process_id', $data['process_id'])->active()->pluck('id')->toArray())->active()->count();
            $user_activities_progress = $user_activities;
            $user_activities_total = $total_activities;


            $user_activities_progressbar = $user_activities > 0 && $total_activities > 0 ? round(((($user_activities * 100 / $total_activities) * 100) / 100)) : 0;
        }

        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'document' => $this->document ?? 'Sin documento',
            'module' => $this->resource->subworkspace?->name ?? 'No module',
            'active' => !!$this->active,
            'absences' => $user_absences,
            'percentage' => $user_activities_progressbar,
            'user_absences' => $user_absences,
            'user_activities_progressbar' => $user_activities_progressbar,
            'user_activities_progress' => $user_activities_progress,
            'user_activities_total' => $user_activities_total,
        ];
    }

    public static function customCollection($resource, $data): AnonymousResourceCollection
    {
      self::$data = $data;
      return parent::collection($resource);
    }
}
