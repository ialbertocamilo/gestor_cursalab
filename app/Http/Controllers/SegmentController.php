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

    public function edit(Request $request)
    {
        $workspace = session('workspace');

        $criteria = Segment::getCriteriaByWorkspace($workspace);

        $segments = Segment::getSegmentsByModel($criteria, $request->model_type, $request->model_id);

        return $this->success(compact('criteria', 'segments'));
    }

    public function create(Request $request)
    {
        $workspace = session('workspace');

        $criteria = Segment::getCriteriaByWorkspace($workspace);

        $segments = Segment::getSegmentsByModel($criteria, $request->model_type, $request->model_id);

        // SegmentResource::collection($blocks);

        return $this->success(compact('criteria', 'segments'));
    }

    public function store(Request $request)
    {
        info($request->all());

        // return true;


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
                // $segment->values()->createMany($values);
            }

            info('values');
            info($values);

            $segment->values()->sync($values);
        }
    }

}
