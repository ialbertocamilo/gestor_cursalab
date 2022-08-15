<?php

namespace App\Http\Controllers;

use App\Models\CriterionValue;
use App\Models\Criterion;
use App\Models\Taxonomy;
use App\Models\Segment;

use Illuminate\Http\Request;
use App\Http\Requests\SegmentRequest;
use App\Http\Resources\SegmentResource;
// use App\Http\Controllers\ZoomApi;

class SegmentController extends Controller
{
    public function search(Request $request)
    {
        $blocks = Segment::search($request);

        SegmentResource::collection($blocks);

        return $this->success($blocks);
    }

    public function create(Request $request)
    {
        $workspace = session('workspace');

        $criteria = Criterion::select('id', 'name', 'position', 'code')->with(['values' => function($q) use ($workspace){
                $values = CriterionValue::whereRelation('workspaces', 'id', $workspace['id'])->get();
                // $q->select('id', 'value_text', 'position');
                $q->whereIn('id', $values->pluck('id')->toArray());
            }])
            ->whereHas('workspaces', function($q) use ($workspace){
                $q->where('workspace_id', $workspace['id']);
            })
            ->get();

        if ($request->model_type AND $request->model_id) {

            $segments = Segment::with(['values' => ['type:id,name,code', 'criterion:id,name,code,position', 'criterion_value:id,value_text,code']])
                ->where('model_type', $request->model_type)
                ->where('model_id', $request->model_id)
                ->get();

            foreach ($segments as $key => $segment)
            {
                // $grouped = $segment->values->groupBy('criterion_id');
                $criteria_selected = $segment->values->unique('criterion');

                $segment->criteria_selected = $criteria_selected;
            }

        } else {
            $segments = [];
        }

        // SegmentResource::collection($blocks);

        return $this->success(compact('criteria', 'segments'));
    }

    public function store(Request $request)
    {
        
        info($request->all());
    }

}
