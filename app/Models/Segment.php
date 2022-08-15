<?php

namespace App\Models;

class Segment extends BaseModel
{
    protected $fillable = [
        'name', 'model_id', 'model_type', 'active'
    ];

    // public function courses()
    // {
    //     return $this->belongsToMany(Course::class, 'segment_course');
    // }

    // public function requirements()
    // {
    //     return $this->hasMany(SegmentRequirement::class);
    // }

    // public function criterion_values()
    // {
    //     return $this->belongsToMany(CriterionValue::class);
    // }

    public function model()
    {
        return $this->morphTo();
    }

    public function values()
    {
        return $this->hasManySync(SegmentValue::class);
        // return $this->hasMany(SegmentValue::class);
    }

    protected function getCriteriaByWorkspace($workspace)
    {
        return Criterion::select('id', 'name', 'position', 'code')->with(['values' => function($q) use ($workspace){
                $values = CriterionValue::whereRelation('workspaces', 'id', $workspace['id'])->get();
                // $q->select('id', 'value_text', 'position');
                $q->whereIn('id', $values->pluck('id')->toArray());
            }])
            ->whereHas('workspaces', function($q) use ($workspace){
                $q->where('workspace_id', $workspace['id']);
            })
            ->get();
    }

    protected function getSegmentsByModel($criteria, $model_type, $model_id)
    {
        if ($model_type AND $model_id) {

            $segments = Segment::with(['values' => ['type:id,name,code', 'criterion:id,name,code,position', 'criterion_value:id,value_text']])
                ->where('model_type', $model_type)
                ->where('model_id', $model_id)
                // ->where('model_id', 188)
                ->get();

            // info('segments');
            // info($segments);

            foreach ($segments as $key => $segment)
            {
                // $grouped = $segment->values->groupBy('criterion_id');
                // $values = $segment->values;
                $criteria_selected = $segment->values->unique('criterion')->pluck('criterion')->toArray();

                // $grouped = [];
                // $grouped = $segment->values->where('criterion_id', $criterion->id)->pluck('criterion_value_id')->toArray();

                // foreach ($values as $key => $value) {
                //     $grouped[$value->criterion_id] = $value['criterion_value_id'];
                //     // code...
                // }
                // $grouped = $values->groupBy('criterion_id');

                // info('grouped');
                // info($grouped);
                // $grouped = [];

                foreach($criteria_selected AS $key => $criterion)
                {
                    // $grouped = $segment->values->where('criterion_id', $criterion['id'])->pluck('criterion_value_id')->toArray();
                    $grouped = $segment->values->where('criterion_id', $criterion['id'])->toArray();

                    info('grouped');
                    info($grouped);

                    $segment_values_selected = [];

                    foreach ($grouped as $g) {
                        $new = $g['criterion_value'];
                        $new['segment_value_id'] = $g['id'];

                        $segment_values_selected[] = $new;
                    }


                    // info('segment');
                    // info($segment->id);
                    info('segment_values_selected');
                    info($segment_values_selected);

                    $criteria_selected[$key]['values'] = $criteria->where('id', $criterion['id'])->first()->values ?? [];
                    $criteria_selected[$key]['values_selected'] = $segment_values_selected ?? [];
                    // $criterion->values_selected = $grouped[$criterion->id]['values_selected'] ?? [];
                }

                $segment->criteria_selected = $criteria_selected;
            }

        } else {

            $segments = [];
        }

        return $segments;
    }
}
