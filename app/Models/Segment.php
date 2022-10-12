<?php

namespace App\Models;

use App\Http\Resources\SegmentSearchUsersResource;
use DB;

class Segment extends BaseModel
{
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

    protected function getCriteriaByWorkspace($workspace)
    {
        return Criterion::select('id', 'name', 'position', 'code', 'field_id')
            ->with([
                'field_type:id,name,code',
                'values' => function ($q) use ($workspace) {
                    //                    $values = CriterionValue::whereRelation('workspaces', 'id', $workspace->id)->get();
                    // $q->select('id', 'value_text', 'position');
//                    $q->whereIn('id', $values->pluck('id')->toArray());
                    $q
                        ->select('id', 'criterion_id', 'value_boolean', 'value_date', 'value_text')
                        ->whereRelation('workspaces', 'id', $workspace->id);
//                        ->whereRelation('type', 'code', '<>', 'date');
                }])
            ->whereHas('workspaces', function ($q) use ($workspace) {
                $q->where('workspace_id', $workspace->id);
            })
            ->get();
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
                $segment->criteria_selected = match ($segment->type?->code) {
                    'direct-segmentation' => $this->setDataDirectSegmentation($criteria, $segment),
                    'segmentation-by-document' => $this->setDataSegmentationByDocument($segment),
                    default => [],
                };
            }

        } else {

            $segments = [];
        }

        return $segments;
    }

    public function setDataDirectSegmentation($criteria, Segment $segment)
    {

        $criteria_selected = $segment->values->unique('criterion')->pluck('criterion')->toArray();

        foreach ($criteria_selected as $key => $criterion) {

            $grouped = $segment->values->where('criterion_id', $criterion['id'])->toArray();

            $segment_values_selected = [];

            foreach ($grouped as $g) {

                $criterion_code = $g['criterion']['field_type']['code'];

                if ($criterion_code === 'date'):

                    $starts_at = carbonFromFormat($g['starts_at'])->format('Y-m-d');
                    $finishes_at = carbonFromFormat($g['finishes_at'])->format('Y-m-d');

                    $new['date_range'] = [$starts_at, $finishes_at];
                    $new['start_date'] = $starts_at;
                    $new['end_date'] = $finishes_at;
                    $new['name'] = "{$starts_at} - {$finishes_at}";
                endif;

                if ($criterion_code === 'default'):
                    $new = $g['criterion_value'];
                endif;

                $new['segment_value_id'] = $g['id'];

                $segment_values_selected[] = $new;
            }

            $criteria_selected[$key]['values'] = $criteria->where('id', $criterion['id'])->first()->values ?? [];
            $criteria_selected[$key]['values_selected'] = $segment_values_selected ?? [];
        }

        return $criteria_selected;
    }

    public function setDataSegmentationByDocument(Segment $segment)
    {
        $criteria_selected = User::query()
            ->select('id', 'name', 'surname', 'lastname', 'document')
            ->withWhereHas('criterion_values', function ($q) use ($segment) {
                $q->select('id', 'value_text')
                    ->whereIn('id', $segment->values->pluck('criterion_value_id'))
                    ->whereRelation('criterion', 'code', 'document');
            })
            ->limit(50)->get();

        $temp = [];
        foreach ($criteria_selected as $user) {
            $criterion_value_id = $user->criterion_values->first()->id;

            $temp[] = [
                'segment_value_id' => $segment->values->where('criterion_value_id', $criterion_value_id)->first()?->id,
                'document' => $user->document,
                'fullname' => $user->fullname,
                'criterion_value_id' => $criterion_value_id
            ];
        }

        return $temp;
    }

    protected function storeRequestData($request)
    {
        try {


            DB::beginTransaction();


            $this->storeDirectSegmentation($request);

            $this->storeSegmentationByDocument($request->all());

            DB::commit();

        } catch (\Exception $e) {

            info($e);

            DB::rollBack();

            return $this->error($e->getMessage());
        }

        $message = 'Segmentación actualizada correctamente.';

        cache_clear_model(Course::class);

        return $this->success(['msg' => $message], $message);
    }

    public function storeDirectSegmentation($data)
    {
        $segments_id = array_column($data->segments, 'id');

        Segment::where('model_type', $data->model_type)->where('model_id', $data->model_id)
            ->whereRelation('type', 'code', 'direct-segmentation')
            ->whereNotIn('id', $segments_id)->delete();

        $direct_segmentation = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
        $code_segmentation = Taxonomy::getFirstData('segment', 'code', $data->code);


        foreach ($data->segments as $key => $segment_row) {
            if (count($segment_row['criteria_selected']) == 0) continue;

            $data = [
                'type_id' => $direct_segmentation->id,
                'code_id' => $code_segmentation?->id,
                'model_type' => $data->model_type,
                'model_id' => $data->model_id,
                'name' => 'Nuevo segmento',
                'active' => ACTIVE,
            ];

            $segment = str_contains($segment_row['id'], "new-segment-") ?
                Segment::create($data)
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

            $segment->values()->sync($values);
        }
    }

    public function prepareDefaultValues($criterion)
    {
        $temp = [];
        foreach ($criterion['values_selected'] ?? [] as $value) {

            $temp[] = [
                'id' => $value['segment_value_id'] ?? null,
                'criterion_value_id' => $value['id'],
                'criterion_id' => $criterion['id'],
                'type_id' => NULL,
            ];
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

        $segment->values()->sync($values);
    }

    protected function validateSegmentByUserCriteria($user_criteria, $segment_criteria): bool
    {
        $segment_valid = false;

        foreach ($segment_criteria as $criterion_id => $segment_values) {

            $user_has_criterion_id = $user_criteria[$criterion_id] ?? false;

            if (!$user_has_criterion_id):
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
        return $segment_values->whereIn('criterion_value_id', $user_criterion_value_id_by_criterion)->count() > 0;
    }

    private function validateDateTypeCriteria($segment_values, $user_criterion_value_by_criterion)
    {
        $hasAValidDateRange = false;

        foreach ($segment_values as $date_range) {

            if (!$date_range['starts_at'] && !$date_range['finishes_at']) continue;

            $starts_at = carbonFromFormat($date_range['starts_at'])->format("Y-m-d");
            $finishes_at = carbonFromFormat($date_range['finishes_at'])->format("Y-m-d");

            $user_date_criterion_value = carbonFromFormat($user_criterion_value_by_criterion->first()->value_date);

            $hasAValidDateRange = $user_date_criterion_value->betweenIncluded($starts_at, $finishes_at);

            if ($hasAValidDateRange) break;
        }

        return $hasAValidDateRange;
    }
}
