<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class UserRelationship extends BaseModel
{

    const  SUPERVISOR_DIRECT_SEGMENTATION_NAME = 'Segmentación directa de supervisor';
    const  SEGMENTATION_NAME_BY_SUPERVISORS_DOCUMENT = 'Segmentación por dni de supervisor';

    protected $table = 'user_relationships';

    protected $fillable = [
        'user_id', 'relation_type_id', 'model_id', 'model_type',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'relation_type_id');
    }

    public function criterion_value()
    {
        return $this->belongsTo(CriterionValue::class, 'criterion_value_id');
    }

//    protected function search($request)
//    {
//        $q = User::query();
////        $q = self::query();
//
//        if ($request->code):
//            $q->whereRelation('relationships.type', 'code', $request->code);
//            if ($request->code === 'supervise'):
////                $q->withCount(['supervised_users', 'supervise_segments']);
//                $q->withCount([
//                    'relationships as users_count' => function ($q_relationships) {
//                        $q_relationships->whereRelation('type', 'code', 'supervise')
//                            ->where('model_type', User::class);
//                    },
//                    'relationships as segments_count' => function ($q_relationships) {
//                        $q_relationships->whereRelation('type', 'code', 'supervise')
//                            ->where('model_type', Segment::class);
//                    },
//                ]);
//            endif;
//        endif;
//
//        $q->withWhereHas('subworkspace', function ($query) use ($request) {
//            if ($request->subworkspace)
//                $query->whereIn('id', $request->subworkspace);
//            else
//                $query->whereRelation('parent', 'id', $request->workspace);
//        });
//
//
//        $field = $request->sortBy ?? 'created_at';
//        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
//
//        $q->orderBy($field, $sort);
//
//        return $q->paginate($request->paginate);
//    }

    protected function createRelation($relation_type, $user, $model_type, $model_id)
    {
        $now = now()->format('Y-m-d H:i:s');

        $temp = [
            'user_id' => $user->id,
            'relation_type_id' => $relation_type->id,
            'model_type' => $model_type,
            'model_id' => $model_id,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        self::create($temp);
    }

    protected function getDataToAssignSupervised($supervisor, $type)
    {
        $data = $type === 'dni' ?
            $supervisor->supervised_users
            : $supervisor->supervised_segments;

//        info($data);
        $temp = [];
        foreach ($data as $row) {
            $model = $type === 'dni' ?
                User::find($row->model_id)
                : SegmentValue::where('segment_id', $row->model_id)->get();
//            info($model);
            if ($model)
                if ($type === 'dni')
                    $temp[] = [
                        'id' => $model->id,
                        'fullname' => $model->fullname
                    ];
                else
                    foreach ($model as $segment_value)
                        $temp[] = $segment_value->criterion_value_id;
        }

        return $temp;
    }

    protected function setUsersAsSupervisor($users)
    {
        $direct_segmentation_type = Taxonomy::getFirstData('segment', 'type', 'direct-segmentation');
        $supervise_code_segment = Taxonomy::getFirstData('segment', 'code', 'user-supervise');
        $segments = [];

        foreach ($users as $user) {
            $segment = Segment::firstOrCreate([
                'name' => self::SUPERVISOR_DIRECT_SEGMENTATION_NAME,
                'model_type' => User::class,
                'model_id' => $user->id,
                'active' => ACTIVE,
                'type_id' => $direct_segmentation_type?->id,
                'code_id' => $supervise_code_segment?->id
            ]);

            $subworkspace = $user->subworkspace;

            $values = [
                [
                    'criterion_value_id' => $subworkspace->criterion_value_id,
                    'criterion_id' => $subworkspace->module_criterion_value->criterion_id,
                    'type_id' => null,
                ]
            ];

            $segment->values()->sync($values);
            array_push($segments, $segment);
        }
        return $segments;
    }

    protected function setDataSupervisor($request)
    {
        $supervise_type = Taxonomy::getFirstData('user', 'action', 'supervise');
        $relation_type = Taxonomy::getFirstData('user', 'action', 'supervise');

        $type = $request['type'];
        $supervisor = User::find($request['supervisor']);

//        self::where('user_id', $supervisor->id)->whereRelation('type', 'code', 'supervise')
//            ->where('model_type', $type == 'dni' ? User::class : Segment::class)
//            ->delete();

        $data_supervisor = [];

        switch ($type) {
            case 'dni':
                self::where('user_id', $supervisor->id)->whereRelation('type', 'code', 'supervise')
                    ->where('model_type', User::class)
                    ->delete();
                $resources = User::whereIn('document', collect($request['resources'])->pluck('document'))->select('id')->get();
                foreach ($resources as $user)
                    self::createRelation($relation_type, $supervisor, User::class, $user->id);

                break;
            case 'criterios':
                $resources = CriterionValue::query()
                    ->whereIn('id', $request['resources'])
                    ->select('id', 'criterion_id')
                    ->get();
                $segment = Segment::firstOrCreate([
                    'name' => self::SUPERVISOR_DIRECT_SEGMENTATION_NAME,
                    'model_id' => $supervisor->id,
                    'type_id' => $supervise_type->id,
                    'model_type' => User::class,
                    'active' => ACTIVE
                ]);

                foreach ($resources as $resource) {
                    $segment_value = $segment->values->where('criterion_value_id', $resource->id)->first();

                    $data_supervisor[] = [
                        'id' => $segment_value->id ?? null,
                        'criterion_value_id' => $resource->id,
                        'criterion_id' => $resource->criterion_id,
                        'type_id' => null
                    ];
                }

                $segment->values()->sync($data_supervisor);
                break;
        }
    }

}
