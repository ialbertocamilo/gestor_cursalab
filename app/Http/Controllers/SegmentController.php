<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Segment;
use App\Models\Taxonomy;
use App\Models\CheckList;
use App\Models\Criterion;
use App\Models\Workspace;

use App\Models\SegmentValue;
use Illuminate\Http\Request;
use App\Models\CriterionValue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\SegmentRequest;
use App\Http\Resources\SegmentResource;
use App\Imports\SegmentSearchByDocumentImport;
use App\Http\Resources\SegmentSearchUsersResource;

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
        //$criteria = Segment::getCriteriaByWorkspaceV2($workspace);
        // $criteria = [];

         $segments = Segment::getSegmentsByModel($criteria,$request->model_type, $request->model_id);
        //$segments = Segment::getSegmentsByModelV2($request->model_type, $request->model_id);


        // $users_count = Segment::usersReached($request->model_type, $request->model_id);
        $users_count = [];

        $courseModules = [];
        if ($request->model_type === 'App\Models\Course') {
            $courseModules = Course::getModulesFromCourseSchools($request->model_id);
        }
        if ($request->model_type === 'App\Models\Checklist') {
            $criteria_entities = $workspace?->checklist_configuration?->entities_criteria;
            if($criteria_entities && is_array($criteria_entities) && count($criteria_entities)>0){
                $criteria = $criteria->where('code', 'module')
                     ->merge($criteria->whereIn('id', $criteria_entities))->all();
            }
        }
        return $this->success(compact('criteria', 'segments', 'users_count', 'courseModules'));
    }

    public function loadModulesFromCourseSchools(Request $request) {
        $modules = Course::getModulesFromCourseSchools($request->courseId);
        $modulesIds = [];
        $subworkspacesIds = [];
        if ($modules) {
            $modulesIds = collect($modules)
                ->unique('module_id')
                ->pluck('module_id')
                ->toArray();

            $subworkspacesIds = Workspace::query()
                ->whereIn('criterion_value_id', $modulesIds)
                ->get()
                ->pluck('id')
                ->toArray();
        }

        return $this->success([
            'modulesIds' => $modulesIds,
            'subworkspacesIds' => $subworkspacesIds,
            'modulesSchools' => $modules
        ]);
    }

    public function create(Request $request)
    {
        $workspace = session('workspace');

        $criteria = Segment::getCriteriaByWorkspaceV2($workspace);

        // $segments = Segment::getSegmentsByModel($criteria, $request->model_type, $request->model_id);
        $segments = [];

        $courseModules = [];
        if ($request->model_type === 'App\Models\Course') {
            $courseModules = Course::getModulesFromCourseSchools($request->model_id);
        }
        // dd($request->model_type === 'App\Models\Checklist',$request->model_type,$workspace?->checklist_configuration?->entities_criteria);
        if ($request->model_type === 'App\Models\Checklist' && $request->model_id) {
            $criteria_entities = $workspace?->checklist_configuration?->entities_criteria;
            $cheklist = CheckList::select('modality_id')->with('modality:id,name,code')->where('id',$request->model_id)->first();
            if($cheklist?->modality?->code == 'qualify_entity' && $criteria_entities && is_array($criteria_entities) && count($criteria_entities)>0){
                $criteria = collect($criteria);
                $criteria = $criteria->where('code', 'module')
                     ->merge($criteria->whereIn('id', $criteria_entities))->all();
            }
        }
            // SegmentResource::collection($blocks);

        return $this->success(
            compact('criteria', 'segments', 'courseModules')
        );
    }

    public function store(Request $request)
    {
        // return ($request->all());
        $data = $request->all();
        $response = Segment::storeRequestData($request);
        if($data['model_type'] == 'App\\Models\\Course'){
            $course = Course::select('id','modality_id')->where('id',$data['model_id'])->with('modality:id,code')->first();
            if($course?->modality?->code == 'virtual'){
                $course->storeUpdateMeeting();
            }
        }
        return $response;
    }

    public function searchUsers(Request $request)
    {
        $data = $request->all();

        $documents = null;
        $sub_workspaces_id = current_subworkspaces_id();

        if ($request->has('file')) {

            $import = new SegmentSearchByDocumentImport($data);
            Excel::import($import, $data['file']);

            $documents = $import->getProccesedData();
        }

        $query = User::query()->FilterByPlatform()
            ->whereIn('subworkspace_id', $sub_workspaces_id)
            ->withWhereHas('criterion_values', function ($q) use ($data) {
                $q->select('id', 'value_text')
                    // ->where('value_text', 'like', "%{$data['filter_text']}%")
                    ->whereRelation('criterion', 'code', 'document');
            })
            ->when($data['filter_text'] ?? null, function ($q) use ($data) {
                $q->filterText($data['filter_text']);
            })
            ->when($data['omit_documents'] ?? null, function ($q) use ($data) {
                $q->whereNotIn('document', $data['omit_documents']);
            })
            ->when($documents ?? null, function ($q) use ($documents) {
                $q->whereIn('document', $documents);
            })
            ->select('id', 'name', 'surname', 'lastname', 'document');

        // When modulesIds are provided, search users only for those modules,
        // or the entire workspace otherwise

        if ($request->has('modulesIds')) {

            $modulesIds = is_array($data['modulesIds'])
                ? $data['modulesIds']
                : explode(',', $data['modulesIds'] ?? []);

            $users = $query->whereHas('subworkspace', function($q) use ($data, $modulesIds) {
                $q->whereIn('criterion_value_id', $modulesIds);
            });
        } else {
            $workspace = get_current_workspace();
            $users = $query->whereRelation('subworkspace', 'parent_id', $workspace?->id);
        }

        $users = ($request->has('file')) ? $users->get() : $users->limit(50)->get();
        $users_not_found = [];
        if($documents){
            $users_not_found = array_diff($documents,$users->pluck('document')->toArray());
            if(count($users_not_found)){
                $users_not_found = collect($users_not_found)->map(function ($documento) {
                    return (object)['document' => $documento];
                })->toArray();
            }
        }
        $users = SegmentSearchUsersResource::collection($users);
        return $this->success(compact('users','users_not_found'));
    }

    public function syncSegmentValuesType()
    {
        $criterion_value_type = Taxonomy::firstOrCreate([
            'group' => 'segment-value',
            'type' => 'type',
            'code' => 'criterion-value',
            'name' => 'Valor de criterio',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        //        $date_range_type = Taxonomy::firstOrCreate([
        //            'group' => 'segment-value',
        //            'type' => 'type',
        //            'code' => 'date-range',
        //            'name' => 'Rango de fechas',
        //            'active' => ACTIVE,
        //            'position' => 2,
        //        ]);

        SegmentValue::query()->update(['type_id' => $criterion_value_type?->id]);
    }

    public function syncUsersDocumentToCriterionValues()
    {

        $direct_segmentation = Taxonomy::firstOrCreate([
            'group' => 'segment',
            'type' => 'type',
            'code' => 'direct-segmentation',
            'name' => 'Segmentación directa',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::firstOrCreate([
            'group' => 'segment',
            'type' => 'type',
            'code' => 'segmentation-by-document',
            'name' => 'Segmentación por documento',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Segment::query()->update(['type_id' => $direct_segmentation?->id]);

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

        // info(now()->format("Y-m-d H:i:s"));
    }

    public function cloneSegmentation(Request $request) {

        Segment::cloneSegmentation(
            $request->originCourseId,
            $request->destinationCoursesIds
        );

        return $this->success();
    }
}
