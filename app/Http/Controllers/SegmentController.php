<?php

namespace App\Http\Controllers;

use App\Http\Resources\SegmentSearchUsersResource;
use App\Models\CriterionValue;
use App\Models\Criterion;
use App\Models\Taxonomy;
use App\Models\Segment;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Requests\SegmentRequest;
use App\Http\Resources\SegmentResource;
use Illuminate\Support\Facades\DB;

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
//        info($request->all());

        return Segment::storeRequestData($request);
    }

    public function searchUsers(Request $request)
    {
        $data = $request->all();

        $workspace = get_current_workspace();

        $users = User::query()
            ->filterText($data['filter_text'])
            ->select('id', 'name', 'surname', 'lastname', 'document')
            ->withWhereHas('criterion_values', function ($q) use ($data) {
                $q->select('id', 'value_text')
                    ->where('value_text', 'like', "%{$data['filter_text']}%")
                    ->whereRelation('criterion', 'code', 'document');
            })
            ->whereRelation('subworkspace', 'parent_id', $workspace?->id)
            ->limit(50)->get();

//        info($users->count());

        $users = SegmentSearchUsersResource::collection($users);

        return $this->success($users);
    }

    public function syncUsersDocumentToCriterionValues()
    {
        info(now()->format("Y-m-d H:i:s"));

        Taxonomy::create([
            'group' => 'segment',
            'type' => 'type',
            'code' => 'direct-segmentation',
            'name' => 'Segmentación directa',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'segment',
            'type' => 'type',
            'code' => 'segmentation-by-document',
            'name' => 'Segmentación por documento',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        $document_criterion = Criterion::firstOrCreate(
            [
                'code' => 'document',
                'name' => "Documento",
                'field_id' => Taxonomy::getFirstData('criterion', 'type', 'default')?->id,
            ],
            [
                'position' => NULL,
                'show_in_segmentation' => 1,
                'active' => 1,
            ]
        );

        DB::table('criteria')
            ->where('code', 'documento')
            ->update(
                ['code' => 'document']
            );

        $direct_segmentation = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');

        Segment::whereIn('active', [ACTIVE, INACTIVE])->update(['type_id' => $direct_segmentation?->id]);

        User::whereNotNull('document')->with('subworkspace.parent')
            ->select('id', 'document', 'subworkspace_id')
            ->chunkById(5000, function ($users_chunked) use ($document_criterion) {

                $document_values = CriterionValue::whereRelation('criterion', 'code', 'document')
                    ->whereIn('value_text', $users_chunked->pluck('document')->toArray())->get();

                foreach ($users_chunked as $user) {

                    $document_value = $document_values->where('value_text', $user->document)->first();

                    $criterion_value_data = [
                        'value_text' => $user->document,
                        'criterion_id' => $document_criterion?->id,
                        'workspace_id' => $user->subworkspace?->parent?->id,
                        'active' => ACTIVE
                    ];

                    $document = CriterionValue::storeRequest($criterion_value_data, $document_value);

                    $user->criterion_values()->syncWithoutDetaching([$document?->id]);

                }
            });

        info(now()->format("Y-m-d H:i:s"));
    }

}
