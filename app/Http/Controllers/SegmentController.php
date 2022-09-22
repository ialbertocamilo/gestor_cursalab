<?php

namespace App\Http\Controllers;

use App\Models\CriterionValue;
use App\Models\Criterion;
use App\Models\Taxonomy;
use App\Models\Segment;

use App\Models\User;
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
//        dd($request->all());
        return Segment::storeRequestData($request);
    }

    public function searchUsers(Request $request)
    {
        $data = $request->all();

        $users = User::filterText($data['filter_text'])
            ->select('id', 'name', 'surname', 'lastname', 'document')
            ->limit(50)->get();

        return $this->success(compact('users'));
    }

}
