<?php

namespace App\Models;

use DB;

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
                $values = CriterionValue::whereRelation('workspaces', 'id', $workspace->id)->get();
                // $q->select('id', 'value_text', 'position');
                $q->whereIn('id', $values->pluck('id')->toArray());
            }])
            ->whereHas('workspaces', function($q) use ($workspace){
                $q->where('workspace_id', $workspace->id);
            })
            ->get();
    }

    protected function getSegmentsByModel($criteria, $model_type, $model_id)
    {
        if ($model_type AND $model_id) {

            $segments = Segment::with(['values' => ['type:id,name,code', 'criterion:id,name,code,position', 'criterion_value:id,value_text']])
                ->where('model_type', $model_type)
                ->where('model_id', $model_id)
                ->get();

            foreach ($segments as $key => $segment)
            {
                $criteria_selected = $segment->values->unique('criterion')->pluck('criterion')->toArray();

                foreach($criteria_selected AS $key => $criterion)
                {
                    $grouped = $segment->values->where('criterion_id', $criterion['id'])->toArray();

                    $segment_values_selected = [];

                    foreach ($grouped as $g) {
                        $new = $g['criterion_value'];
                        $new['segment_value_id'] = $g['id'];

                        $segment_values_selected[] = $new;
                    }

                    $criteria_selected[$key]['values'] = $criteria->where('id', $criterion['id'])->first()->values ?? [];
                    $criteria_selected[$key]['values_selected'] = $segment_values_selected ?? [];
                }

                $segment->criteria_selected = $criteria_selected;
            }

        } else {

            $segments = [];
        }

        return $segments;
    }

    protected function storeRequestData($request)
    {
        try {

            $segments_id = array_column($request->segments, 'id');

            DB::beginTransaction();

            Segment::where('model_type', $request->model_type)->where('model_id', $request->model_id)
                    ->whereNotIn('id', $segments_id)->delete();

            foreach ($request->segments as $key => $segment_row) {

                $data = [
                    'model_type' => $request->model_type,
                    'model_id' => $request->model_id,
                    'name' => 'Nuevo segmento',
                    'active' => ACTIVE,
                ];

                $segment = !empty($segment_row['id']) ? Segment::find($segment_row['id']) : Segment::create($data);

                $values = [];

                foreach ($segment_row['criteria_selected'] ?? [] as $criterion) {

                    foreach ($criterion['values_selected'] ?? [] as $value) {

                        $values[] = [
                            'id' => $value['segment_value_id'] ?? null,
                            'criterion_value_id' => $value['id'],
                            'criterion_id' => $criterion['id'],
                            'type_id' => NULL,
                        ];
                    }
                }

                $segment->values()->sync($values);
            }

            DB::commit();

        }catch (\Exception $e){

            info($e);

            DB::rollBack();

            return $this->error($e->getMessage());
        }

        $message = 'Segmentación actualizada correctamente.';

        return $this->success(['msg' => $message], $message);
    }
}
