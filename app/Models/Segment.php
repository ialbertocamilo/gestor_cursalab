<?php

namespace App\Models;

use App\Http\Resources\SegmentSearchUsersResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Segment extends BaseModel{
    protected $fillable = [
        'name', 'model_id', 'model_type', 'active', 'type_id', 'code_id'
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

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function code()
    {
        return $this->belongsTo(Taxonomy::class, 'code_id');
    }

    public function values()
    {
        return $this->hasManySync(SegmentValue::class);
        // return $this->hasMany(SegmentValue::class);
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }
    protected function getCriteriaByWorkspace($workspace)
    {
        $criteria = $workspace->criterionWorkspace;

        $criteria_config = $criteria->where('pivot.available_in_segmentation', 1)->pluck('id');

        return Criterion::select('id', 'name', 'position', 'code', 'field_id')
            ->with([
                'field_type:id,name,code',
                'values' => function ($q) use ($workspace) {

                    //                    $values = CriterionValue::whereRelation('workspaces', 'id', $workspace->id)->get();
                    // $q->select('id', 'value_text', 'position');
                    //                    $q->whereIn('id', $values->pluck('id')->toArray());
                    $q
                        ->select('id', 'criterion_id', 'value_boolean', 'value_date', 'value_text')
                        ->whereRelation('workspaces', 'id', $workspace->id)
                        ->whereRelation('criterion.field_type', 'code', '<>', 'date');
                }
            ])
            ->whereIn('id', $criteria_config)
            ->whereHas('workspaces', function ($q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            })
            ->get();
    }

    protected function getCriteriaByWorkspaceV2($workspace)
{
    $results = DB::select('
        SELECT
            c.id,
            c.name,
            c.code,
            t.name AS taxonomy_name,
            t.code AS taxonomy_code,
            cv.value_text,
            cv.value_date,
            c.field_id,
            cv.id as criterion_value_id

        FROM
            criterion_value_workspace cvw
        LEFT JOIN criterion_values cv ON
            cvw.criterion_value_id = cv.id
        JOIN criteria c ON
            cv.criterion_id = c.id
        JOIN taxonomies t ON
            c.field_id = t.id
        WHERE
            cvw.workspace_id = ?
            AND cv.criterion_id IN (
                SELECT
                    criterion_id
                FROM
                    criterion_workspace cw
                WHERE
                    available_in_segmentation = 1
                    AND workspace_id = ?
            )
    ', [$workspace->id, $workspace->id]);

    $groupedCriteria = [];

    foreach ($results as $result) {
        $code = $result->code;

        if (!isset($groupedCriteria[$code])) {
            $groupedCriteria[$code] = [
                'id' => $result->id,
                'name' => $result->name,
                'code' => $result->code,
                'fiel_id' => $result->field_id,
                'field_type' => [
                    'id' => $result->field_id,
                    'name' => $result->taxonomy_name,
                    'code' => $result->taxonomy_code,
                ],
                'values' => [

                        [
                            'id' => $result->criterion_value_id,
                            'criterion_id' => $result->id,
                            'value_boolean' => 0,
                            'value_date' => $result->value_date,
                            'value_text' => $result->value_text,
                        ],

                ],
            ];
        } else {
            $groupedCriteria[$code]['values'][] = [
                'id' => $result->criterion_value_id,
                'criterion_id' => $result->id,
                'value_boolean' => 0,
                'value_date' => $result->value_date,
                'value_text' => $result->value_text,
            ];
        }
    }

    $groupedCriteria = array_values($groupedCriteria);

    return $groupedCriteria;
}

    protected function getSegmentsByModel($criteria, $model_type, $model_id)
    {
        if ($model_type and $model_id) {

            $segments = Segment::with([
                'type:id,name,code',
                'values' => [
                    'type:id,name,code',
                    'criterion:id,name,code,field_id',
                    'criterion' => [
                        'field_type:id,name,code'
                    ],
                    'criterion_value:id,value_text'
                ]
            ])
                ->where('model_type', $model_type)
                ->where('model_id', $model_id)
                ->select('id', 'type_id', 'name', 'model_id', 'model_type')
                ->get();

            foreach ($segments as $segment) {

                $segment->type_code = $segment->type?->code;

                $direct_segmentation = ($segment->type_code == 'direct-segmentation') ? $this->setDataDirectSegmentation($criteria, $segment) : [];
                $segmentation_by_document = ($segment->type_code == 'segmentation-by-document') ? $this->setDataSegmentationByDocument($segment) : [];

                $segment->criteria_selected = match ($segment->type?->code) {
                    'direct-segmentation' => $direct_segmentation,
                    'segmentation-by-document' => $segmentation_by_document,
                    default => [],
                };
                $segment->direct_segmentation = $direct_segmentation;
                $segment->segmentation_by_document = $segmentation_by_document;
            }
        } else {

            $segments = [];
        }

        return $segments;
    }

    public static function getSegmentsByModelV2($model_type, $model_id)
    {
        $query = self::segmentsQuery($model_type, $model_id);
        $segments = self::getSegmentsByCode($query);
        return $segments;

    }
    public static function getSegmentsByCode($results ) {
        $data = [];

        foreach ($results as $result) {
            // Agregar datos del primer conjunto
            $data[$result->id]['id'] = $result->id;
            $data[$result->id]['name'] = $result->name;
            $data[$result->id]['model_id'] = $result->model_id;
            $data[$result->id]['model_type'] = $result->model_type;
            $data[$result->id]['type_id'] = $result->type_id;
            $data[$result->id]['type_code'] = $result->code;
            $data[$result->id]['type']['id'] = $result->field_id;
            $data[$result->id]['type']['name'] = $result->name;
            $data[$result->id]['type']['code'] = $result->code;

            $criteriaId = $result->criteria_id;



            if ($result->code === 'direct-segmentation'){
                $data[$result->id]['criteria_selected'][$criteriaId] = [
                    'name' => $result->criteria_name,
                    'code' => $result->criteria_code,
                    'id' => $criteriaId,
                    'field_id' => $result->field_id,
                    'field_type' => [
                        'id' => $result->field_id,
                        'name' => $result->criteria_taxonomie_name,
                        'code' => $result->criteria_taxonomie_code,
                    ]
                ];
                $data[$result->id]['direct_segmentation'][$criteriaId] = [
                    'name' => $result->criteria_name,
                    'code' => $result->criteria_code,
                    'id' => $criteriaId,
                    'field_id' => $result->field_id,
                    'field_type' => [
                        'id' => $result->field_id,
                        'name' => $result->criteria_taxonomie_name,
                        'code' => $result->criteria_taxonomie_code,
                    ],
                ];
                $data[$result->id]['direct_segmentation'][$criteriaId]['values'][] = [
                    'id' => $result->criterion_value_id,
                    'criterion_id'=> $result->criteria_id,
                    'value_text' => $result->value_text,
                    'value_date' => $result->value_date,
                    'value_boolean'=> 0,
                ];
                if ($result->criteria_taxonomie_code === 'date'){
                    $starts_at = carbonFromFormat($result->starts_at)->format('Y-m-d');
                    $finishes_at = carbonFromFormat($result->finishes_at)->format('Y-m-d');
                    $data[$result->id]['direct_segmentation'][$criteriaId]['values_selected'][] = [
                        'id' => $result->criterion_value_id,
                        'value_text' => $result->value_text,
                        'segment_value_id' => $result->segment_value_id,
                        'start_date' => $starts_at,
                        'end_date' => $finishes_at,
                        'name' => $starts_at .' - '. $finishes_at,
                        'date_range' => [
                            $starts_at, $finishes_at
                        ]
                    ];
                    $data[$result->id]['criteria_selected'][$criteriaId]['values_selected'][] = [
                        'id' => $result->criterion_value_id,
                        'value_text' => $result->value_text,
                        'segment_value_id' => $result->segment_value_id,
                        'start_date' => $starts_at,
                        'end_date' => $finishes_at,
                        'name' => $starts_at .' - '. $finishes_at,
                        'date_range' => [
                            $starts_at, $finishes_at
                        ]
                    ];
                } else {
                    $data[$result->id]['direct_segmentation'][$criteriaId]['values_selected'][] = [
                        'id' => $result->criterion_value_id,
                        'value_text' => $result->value_text,
                        'segment_value_id' => $result->segment_value_id,
                    ];
                    $data[$result->id]['criteria_selected'][$criteriaId]['values_selected'][] = [
                        'id' => $result->criterion_value_id,
                        'value_text' => $result->value_text,
                        'segment_value_id' => $result->segment_value_id,
                    ];
                }
                $data[$result->id]['criteria_selected'][$criteriaId]['values'][] = [
                    'id' => $result->criterion_value_id,
                    'criterion_id'=> $result->criteria_id,
                    'value_text' => $result->value_text,
                    'value_date' => $result->value_date,
                    'value_boolean'=> 0,
                ];

                $data[$result->id]['direct_segmentation'] = array_values($data[$result->id]['direct_segmentation']);
            }
            if ($result->code === 'segmentation-by-document'){
                $data[$result->id]['criteria_selected'][] = [
                    'criterion_value_id' => $result->criterion_value_id,
                    'document' => $result->value_text,
                    'fullname' => $result->fullName,
                    'segment_value_id' => $result->segment_value_id,
                ];

                $data[$result->id]['segmentation_by_document'][] = [
                    'criterion_value_id' => $result->criterion_value_id,
                    'document' => $result->value_text,
                    'fullname' => $result->fullName,
                    'segment_value_id' => $result->segment_value_id,
                ];

            }

            $data[$result->id]['criteria_selected'] = array_values($data[$result->id]['criteria_selected']);

        }

        return array_values($data);
    }



    public function setDataDirectSegmentation($criteria, Segment $segment)
    {

        $criteria_selected = $segment->values->unique('criterion')->pluck('criterion')->toArray();

        foreach ($criteria_selected as $key => $criterion) {

            if (!$criterion) continue;

            $criterion_used = $criteria->where('id', $criterion['id'])->first();

            // Remove criterion if no longer in config for segmentation
            if (!$criterion_used) {
                unset($criteria_selected[$key]);
                continue;
            }

            $grouped = $segment->values->where('criterion_id', $criterion['id'])->toArray();

            $segment_values_selected = [];

            foreach ($grouped as $g) {

                $criterion_code = $g['criterion']['field_type']['code'];

                if ($criterion_code === 'date') :

                    $starts_at = carbonFromFormat($g['starts_at'])->format('Y-m-d');
                    $finishes_at = carbonFromFormat($g['finishes_at'])->format('Y-m-d');

                    $new['date_range'] = [$starts_at, $finishes_at];
                    $new['start_date'] = $starts_at;
                    $new['end_date'] = $finishes_at;
                    $new['name'] = "{$starts_at} - {$finishes_at}";
                endif;

                if ($criterion_code === 'default') :
                    $new = $g['criterion_value'];
                endif;

                $new['segment_value_id'] = $g['id'];

                $segment_values_selected[] = $new;
            }

            // $criteria_selected[$key]['values'] = $criteria->where('id', $criterion['id'])->first()->values ?? [];
            $criteria_selected[$key]['values'] = $criterion_used->values ?? [];
            $criteria_selected[$key]['values_selected'] = $segment_values_selected ?? [];
        }

        return $criteria_selected;
    }

    public function setDataSegmentationByDocument(Segment $segment)
    {
        $criterion_value_documents = CriterionValue::whereIn('id', $segment->values->pluck('criterion_value_id'))
            ->pluck('value_text');

        $criteria_selected = User::query()
            ->select('id', 'name', 'surname', 'lastname', 'document')
            ->whereIn('document', $criterion_value_documents)
            //->withWhereHas('criterion_values', function ($q) use ($segment) {
            ->with('criterion_values', function ($q) use ($segment) {
                $q->select('id', 'value_text')
//                    ->whereIn('id', $segment->values->pluck('criterion_value_id'))
                    ->whereRelation('criterion', 'code', 'document');
            })
//            ->limit(50)
            ->get();

        $segment_values_id = $segment->values->pluck('id', 'criterion_value_id')->toArray();

        $temp = [];
        foreach ($criteria_selected as $user) {

            if ($user->criterion_values->first()) {

                $criterion_value_id = $user->criterion_values->first()->id;
                $temp[] = [
                    // 'segment_value_id' => $segment->values->where('criterion_value_id', $criterion_value_id)->first()?->id,
                    'segment_value_id' => $segment_values_id[$criterion_value_id] ?? null,
                    'document' => $user->document,
                    'fullname' => $user->fullname,
                    'criterion_value_id' => $criterion_value_id
                ];
            }
        }

        return $temp;
    }

    protected function storeRequestData($request)
    {
        try {

            DB::beginTransaction();

            $this->storeDirectSegmentation($request);

            $this->storeSegmentationByDocument($request->all());
            //colocar usuarios en cola para actualizaciónes masivas
            if($request->model_type == 'App\\Models\\Course'){
                $course = Course::find($request->model_id);
                Summary::updateUsersByCourse($course,null,false,false,'segmented',send_notification:true);
            }

            DB::commit();
        } catch (\Exception $e) {

            info($e);

            DB::rollBack();

            return $this->error($e->getMessage());
        }


        // $users_count = Segment::usersReached($request->model_type, $request->model_id);

        // $message = "Segmentación actualizada correctamente. {$users_count} usuarios alcanzados.";
        $message = "Segmentación actualizada correctamente.";

        cache_clear_model(Course::class);

        return $this->success(['msg' => $message], $message);
    }

    private function updateSegmentToLaunchObeserver($data)
    {
        $segments_id = array_column($data->segments, 'id');
//        info($segments_id);
//        self::whereIn('id', $segments_id)->update([
//            'updated_at' => now()
//        ]);
        $segments = self::whereIn('id', $segments_id)->get();
        foreach ($segments as $segment) {
            $segment->update([
                'name' => $segment->name."-".rand(1,100),
                'updated_at' => now()
            ]);
        }
    }

    public function storeDirectSegmentation($data)
    {
        // $this->updateSegmentToLaunchObeserver($data);

        $segments_id = array_column($data->segments, 'id');

        Segment::where('model_type', $data->model_type)->where('model_id', $data->model_id)
            ->whereRelation('type', 'code', 'direct-segmentation')
            ->whereNotIn('id', $segments_id)->delete();

        $direct_segmentation = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
        $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data->code);


        foreach ($data->segments as $key => $segment_row) {
            if (count($segment_row['criteria_selected']) == 0) continue;

            $segment_data = [
                'type_id' => $direct_segmentation->id,
                'code_id' => $code_segmentation?->id,
                'model_type' => $data->model_type,
                'model_id' => $data->model_id,
                'name' => 'Nuevo segmento',
                'active' => ACTIVE,
            ];

            $segment = str_contains($segment_row['id'], "new-segment-") ?
                Segment::create($segment_data)
                : Segment::find($segment_row['id']);

            $values = [];

            foreach ($segment_row['criteria_selected'] ?? [] as $criterion) {

                $temp_values = match ($criterion['field_type']['code']) {
                    'default' => $this->prepareDefaultValues($criterion),
                    'date' => $this->prepareDateRangeValues($criterion),
                    default => [],
                };

                $values = array_merge($values, $temp_values);
            }

            if ($segment)
                $segment->values()->sync($values);
        }
    }

    public function prepareDefaultValues($criterion)
    {
        $temp = [];
        foreach ($criterion['values_selected'] ?? [] as $value) {

            if (isset($value['id']) && isset($criterion['id'])) {
                $temp[] = [
                    'id' => $value['segment_value_id'] ?? null,
                    'criterion_value_id' => $value['id'],
                    'criterion_id' => $criterion['id'],
                    'type_id' => NULL,
                ];
            }
        }

        return $temp;
    }

    public function prepareDateRangeValues($criterion)
    {
        $temp = [];

        foreach ($criterion['values_selected'] ?? [] as $value) {

            $start_date = $value['start_date'];
            $end_date = $value['end_date'];

            $temp[] = [
                'id' => $value['segment_value_id'] ?? null,
                'criterion_id' => $criterion['id'],
                'starts_at' => $start_date,
                'finishes_at' => $end_date,
                'type_id' => NULL,
            ];
        }

        return $temp;
    }

    public function storeSegmentationByDocument($data)
    {
        $segmentation_by_document = Taxonomy::getFirstData('segment', 'type', 'segmentation-by-document');
        $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data['code']);

        if (count($data['segment_by_document']['criteria_selected']) === 0) {
            $segment = self::where('active', ACTIVE)
                ->where('model_id', $data['model_id'])
                ->where('model_type', $data['model_type'])
                ->where('type_id', $segmentation_by_document->id)
                ->first();

            if ($segment) {
                $segment->values()->delete();
                $segment->delete();
            }
            return;
        }


        $segment = self::firstOrCreate(
            [
                'model_id' => $data['model_id'],
                'model_type' => $data['model_type'],
                'type_id' => $segmentation_by_document->id,
                'code_id' => $code_segmentation?->id,
            ],
            ['name' => "Segmentación por documento", 'active' => ACTIVE]
        );

        $segment->values()->delete();

        $document_criterion = Criterion::where('code', 'document')->first();

        $values = [];

        foreach ($data['segment_by_document']['criteria_selected'] ?? [] as $value) {

            $values[] = [
                'id' => $value['segment_value_id'] ?? null,
                'criterion_value_id' => $value['criterion_value_id'],
                'criterion_id' => $document_criterion->id,
                'type_id' => NULL,
            ];
        }
        $data['segments'] = [$segment];
        $data = (object) $data;
        // $this->updateSegmentToLaunchObeserver($data);
        $segment->values()->sync($values);
    }

    protected function validateSegmentByUserCriteria($user_criteria, $segment_criteria): bool
    {
        $segment_valid = false;

        foreach ($segment_criteria as $criterion_id => $segment_values) {

            $user_has_criterion_id = $user_criteria[$criterion_id] ?? false;

            if (!$user_has_criterion_id) :
                $segment_valid = false;
                break;
            endif;

            $segment_valid = $this->validateValueSegmentCriteriaByUser($segment_values, $user_criteria[$criterion_id]);

            if (!$segment_valid) break;
        }

        return $segment_valid;
    }

    private function validateValueSegmentCriteriaByUser($segment_values, $user_criterion_value_by_criterion)
    {
        $criterion_code = $user_criterion_value_by_criterion->first()?->criterion?->field_type?->code;
        $user_criterion_value_id_by_criterion = $user_criterion_value_by_criterion->pluck('id');

        return match ($criterion_code) {
            'default' => $this->validateDefaultTypeCriteria($segment_values, $user_criterion_value_id_by_criterion),
            'date' => $this->validateDateTypeCriteria($segment_values, $user_criterion_value_by_criterion),
            default => false,
        };
    }

    private function validateDefaultTypeCriteria($segment_values, $user_criterion_value_id_by_criterion)
    {
        $criterion_id = $segment_values->first()->criterion_id;

        if ($criterion_id == 48) {

            // $temp_segment_values = $segment_values->keyBy('criterion_value_id');
            // $row = $temp_segment_values->get($user_criterion_value_id_by_criterion[0]);

            $row = $segment_values->where('criterion_value_id', $user_criterion_value_id_by_criterion[0])->first();
            // $rows = $segment_values->pluck('criterion_value_id')->toArray();

            // return in_array($user_criterion_value_id_by_criterion[0], $rows) ? true : false;
            return $row ? true : false;

        } else {

            return $segment_values->whereIn('criterion_value_id', $user_criterion_value_id_by_criterion)->count() > 0;
        }
    }

    private function validateDateTypeCriteria($segment_values, $user_criterion_value_by_criterion)
    {
        $hasAValidDateRange = false;

        foreach ($segment_values as $date_range) {

            if (!$date_range['starts_at'] && !$date_range['finishes_at']) continue;

            $starts_at = carbonFromFormat($date_range['starts_at']);
            $finishes_at = carbonFromFormat($date_range['finishes_at']);

            $user_date_criterion_value = carbonFromFormat($user_criterion_value_by_criterion->first()->value_date, "Y-m-d");

            $hasAValidDateRange = $user_date_criterion_value->betweenIncluded($starts_at, $finishes_at);

            if ($hasAValidDateRange) break;
        }

        return $hasAValidDateRange;
    }

    /**
     * Extract criterion value ids from segment
     * values related to a supervisor
     *
     * @param int $supervisorId
     * @return array|void
     */
    public static function loadSupervisorSegmentCriterionValues(int $supervisorId)
    {
        // Load taxonomy for supervisors

        $supervisorTaxonomy = Taxonomy::query()
            ->where('group', 'segment')
            ->where('code', 'user-supervise')
            ->where('type', 'code')
            ->where('active', 1)
            ->first();

        // Load criteria values ids
        return Segment::where('model_id',$supervisorId)
            ->where('code_id',$supervisorTaxonomy->id)
            ->with('values')
            ->get();

        // return Segment::join('segments_values', 'segments.id', '=', 'segments_values.segment_id')
        //     ->where('segments.model_id', $supervisorId)
        //     ->where('segments.code_id', $supervisorTaxonomy->id)
        //     ->where('segments.active', 1)
        //     ->whereNull('segments_values.deleted_at')
        //     ->select([
        //         'segments_values.criterion_id',
        //         'segments_values.criterion_value_id'
        //     ])
        //     ->orderBy('criterion_id')
        //     ->get();
    }

    public static function loadSupervisorSegmentUsersIds($supervisorId, $workspaceId): array|string
    {

        $segments = Segment::loadSupervisorSegmentCriterionValues($supervisorId);

        if (count($segments) === 0) return [];
        //Function to get
        $course = new Course();
        return $course->usersSegmented($segments,'users_id');
        // Generate conditions

        // $criterionIds = [];
        // $previousCriterionId = null;
        // $WHERE = [];
        // foreach ($criterionValues as $value) {

        //     $criterionId = $value->criterion_id;

        //     if ($criterionId !== $previousCriterionId) {

        //         if (!in_array($criterionId, $criterionIds))
        //             $criterionIds[] = $criterionId;

        //         $previousCriterionId = $criterionId;

        //         $criterionValuesIds = $criterionValues->where('criterion_id', $criterionId)
        //             ->pluck('criterion_value_id')
        //             ->toArray();
        //         $WHERE[] = "(
        //             scv.criterion_id = $criterionId and
        //             scv.criterion_value_id in (" . implode(',', $criterionValuesIds) . ")
        //         )";
        //     }
        // }

        // // When no condition was generated, stop method execution

        // if (count($WHERE) === 0) return [];

        // $WHERE = implode(' or ', $WHERE);
        // $criterionCount = count($criterionIds);
        // $criterionIds = implode(',', $criterionIds);
        // $modulesIds = Workspace::loadSubWorkspacesIds($workspaceId)->toArray();
        // $query = "
        //     select
        //         user_id
        //     from (
        //         -- Users' criterion values
        //         select
        //             cvu.user_id,
        //             cvu.criterion_value_id,
        //             cv.criterion_id
        //         from
        //             criterion_value_user cvu
        //                 inner join criterion_values cv on cv.id = cvu.criterion_value_id
        //                 inner join users u on u.id = cvu.user_id
        //         where cv.criterion_id in ($criterionIds) and
        //               u.subworkspace_id in (" . implode(',', $modulesIds) . ")
        //     ) scv
        //     where
        //         $WHERE

        //     group by
        //         user_id
        //     having count(user_id) = $criterionCount
        // ";

        // return collect(DB::select(DB::raw($query)))
        //     ->pluck('user_id')
        //     ->toArray();
    }

    protected function usersReached($model, $model_id)
    {
        $row = $model::find($model_id)->load('segments');

        // $users = $row->getUsersBySegmentations();
        $totals = $row->getUsersBySegmentation();

        // $users = $row->usersSegmented($row->segments, 'count');

        return $totals;
    }

    public function storeSegmentationByDocumentForm($data)
    {
        $segmentation_by_document = Taxonomy::getFirstData('segment', 'type', 'segmentation-by-document');
        $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data['code']);

        if (count($data['segment_by_document']['segmentation_by_document']) === 0) {
            $segment = self::where('active', ACTIVE)
                ->where('model_id', $data['model_id'])
                ->where('model_type', $data['model_type'])
                ->where('type_id', $segmentation_by_document->id)
                ->first();

            if ($segment) {
                $segment->values()->delete();
                $segment->delete();
            }
            return;
        }


        $segment = self::firstOrCreate(
            [
                'model_id' => $data['model_id'],
                'model_type' => $data['model_type'],
                'type_id' => $segmentation_by_document->id,
                'code_id' => $code_segmentation?->id,
            ],
            ['name' => "Segmentación por documento", 'active' => ACTIVE]
        );

        $segment->values()->delete();

        $document_criterion = Criterion::where('code', 'document')->first();

        $values = [];

        foreach ($data['segment_by_document']['segmentation_by_document'] ?? [] as $value) {

            $values[] = [
                'id' => $value['segment_value_id'] ?? null,
                'criterion_value_id' => $value['criterion_value_id'],
                'criterion_id' => $document_criterion->id,
                'type_id' => NULL,
            ];
        }

        $segment->values()->sync($values);
    }

    public function preDirectSegmentation($data)
    {
        $list_segments_direct = [];

        $direct_segmentation = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
        $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data->code);

        foreach ($data->segments as $key => $segment_row) {
            if (count($segment_row['criteria_selected']) == 0) continue;

            $segment_data = [
                'type_id' => $direct_segmentation->id,
                'code_id' => $code_segmentation?->id,
                'model_type' => $data->model_type,
                'model_id' => $data->model_id,
                'name' => 'Nuevo segmento',
                'active' => ACTIVE,
            ];

            $segment = new Segment($segment_data);

            $values = [];

            foreach ($segment_row['criteria_selected'] ?? [] as $criterion) {

                $temp_values = match ($criterion['field_type']['code']) {
                    'default' => (new Segment)->prepareDefaultValues($criterion),
                    'date' => (new Segment)->prepareDateRangeValues($criterion),
                    default => [],
                };

                $values = array_merge($values, $temp_values);
            }
            $model_values = collect();
            foreach($values as $val) {

                $segment_value = new SegmentValue($val);
                $model_values->push($segment_value);
            }
            $segment->values = $model_values;
            array_push($list_segments_direct, $segment);
        }
        return $list_segments_direct;
    }


    public function preSegmentationByDocument($data)
    {
        $list_segments_document = [];

        $segmentation_by_document = Taxonomy::getFirstData('segment', 'type', 'segmentation-by-document');
        $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data['code']);

        $segment_data = [
            'type_id' => $segmentation_by_document->id,
            'code_id' => $code_segmentation?->id,
            'model_type' => $data['model_type'],
            'model_id' => $data['model_id'],
            'name' => 'Segmentación por documento',
            'active' => ACTIVE,
        ];

        $segment = new Segment($segment_data);

        $document_criterion = Criterion::where('code', 'document')->first();

        $values = [];

        foreach ($data['segment_by_document']['segmentation_by_document'] ?? [] as $value) {

            $values[] = [
                'id' => $value['segment_value_id'] ?? null,
                'criterion_value_id' => $value['criterion_value_id'],
                'criterion_id' => $document_criterion->id,
                'type_id' => NULL,
            ];
        }

        $model_values = collect();
        foreach($values as $val) {

            $segment_value = new SegmentValue($val);
            $model_values->push($segment_value);
        }
        $segment->values = $model_values;
        array_push($list_segments_document, $segment);

        return $list_segments_document;
    }
    public static function segmentsStored ($model_id){
        $segment = DB::select('call getSegmentData(2)');
        return $segment;
    }
    public static function segmentsQuery ($model_type, $model_id){
        if($model_type and $model_id)
        $segments = DB::select(DB::raw("
                SELECT
                    s.id,
                    s.type_id,
                    s.name as segment_name,
                    s.model_id,
                    s.model_type,
                    t2.code,
                    t2.name,
                    c.id as criteria_id,
                    c.code as criteria_code,
                    c.name as criteria_name,
                    c.field_id,
                    t.name as criteria_taxonomie_name,
                    t.code as criteria_taxonomie_code,
                    cv.value_date,
                    cv.value_text,
                    sv.starts_at,
                    sv.finishes_at,
                    CONCAT(u.name,' ',u.lastname) as fullName,
                    sv.id as segment_value_id,
                    cv.id as criterion_value_id
                from
                    segments_values sv
                left join segments s on
                    sv.segment_id = s.id
                left join criterion_values cv on
                    sv.criterion_value_id = cv.id
                left join criteria c on
                    cv.criterion_id = c.id
                left join taxonomies t on
                    c.field_id = t.id
                left join taxonomies t2 on s.type_id = t2.id
                left join users u on cv.value_text = u.document
                WHERE
                    s.model_id = :model_id AND s.model_type = :model_type;
            "),
            ['model_type' => $model_type, 'model_id' => $model_id]);
            return $segments;
    }

    public static function cloneSegmentation ($originCourseId, $destinationCoursesIds) {

        $segments = Segment::getSegmentsByModelV2('App\\Models\\Course', $originCourseId);
        $keys = [
            'name', 'model_type', 'type_id',
        ];

        try {
            DB::beginTransaction();

            foreach ($destinationCoursesIds as $destinationCourseId) {

                // Delete course's old segments and segments values

                self::deleteCourseSegments($destinationCourseId);
            }

            foreach ($segments as $originSegment) {

                $originSegmentValues = SegmentValue::query()
                    ->where('segment_id', $originSegment['id'])
                    ->get()
                    ->toArray();

                // Get values from origin segment

                $segmentTemplate = array_intersect_key($originSegment, array_flip($keys));
                $segmentTemplate['active'] = 1;

                foreach ($destinationCoursesIds as $destinationCourseId) {

                    // Create new segment for current course

                    $segmentTemplate['model_id'] = $destinationCourseId;
                    $newSegment = Segment::create($segmentTemplate);

                    // Create segment values for new segment

                    $destinationSegmentValues = $originSegmentValues;
                    foreach ($destinationSegmentValues as &$segmentValue) {
                        $segmentValue['segment_id'] = $newSegment->id;
                        unset($segmentValue['id']);
                        unset($segmentValue['created_at']);
                        unset($segmentValue['update_at']);
                    }
                    SegmentValue::insert($destinationSegmentValues);
                }
            }

            // Set users for update courses list

            Summary::updateUsersByCourses($destinationCoursesIds);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Transaction failed: ' . $e->getMessage());
        }


        //dd($segments);
    }

    public static function deleteCourseSegments($courseId) {

        $segments = Segment::query()
            ->where('model_id', $courseId)
            ->where('model_type', 'App\\Models\\Course')
            ->get();

        foreach ($segments as $segment) {
            $segment->values()->delete();
            $segment->delete();
        }
    }
}
