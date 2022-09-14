<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class UserRelationship extends BaseModel
{
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

    protected function search($request)
    {
        $q = User::query();
//        $q = self::query();

        if ($request->code):
            $q->whereRelation('relationships.type', 'code', $request->code);
            if ($request->code === 'supervise'):
//                $q->withCount(['supervised_users', 'supervise_segments']);
                $q->withCount([
                    'relationships as users_count' => function ($q_relationships) {
                        $q_relationships->whereRelation('type', 'code', 'supervise')
                            ->where('model_type', User::class);
                    },
                    'relationships as segments_count' => function ($q_relationships) {
                        $q_relationships->whereRelation('type', 'code', 'supervise')
                            ->where('model_type', Segment::class);
                    },
                ]);
            endif;
        endif;

        $q->withWhereHas('subworkspace', function ($query) use ($request) {
            if ($request->subworkspace)
                $query->whereIn('id', $request->subworkspace);
            else
                $query->whereRelation('parent', 'id', $request->workspace);
        });


        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);

    }

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

        info($data);
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
        $relation_type = Taxonomy::getFirstData('user', 'action', 'supervise');

        foreach ($users as $user) {
            // Create segment and segment_values
            $segment = Segment::create([
                'name' => "Segmentación de supervisor",
                'model_type' => User::class,
                'model_id' => $user->id,
                'active' => ACTIVE,
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

            self::createRelation($relation_type, $user, Segment::class, $segment->id);
        }

    }

    protected function setDataSupervisor($request)
    {
        $supervise_type = Taxonomy::getFirstData('user', 'action', 'supervise');
        $relation_type = Taxonomy::getFirstData('user', 'action', 'supervise');

        $type = $request['type'];
        $supervisor = User::find($request['supervisor']);
        self::where('user_id', $supervisor->id)->whereRelation('type', 'code', 'supervise')
            ->where('model_type', $type == 'dni' ? User::class : Segment::class)
            ->delete();
        $data_supervisor = [];

        switch ($type) {
            case 'dni':
                $resources = User::whereIn('document', collect($request['resources'])->pluck('document'))->select('id')->get();
                foreach ($resources as $user)
                    self::createRelation($relation_type, $supervisor, User::class, $user->id);

                break;
            case 'criterios':
                $resources = CriterionValue::whereIn('id', $request['resources'])->select('id')->get();
                $segment = Segment::firstOrCreate([
                        'user_id' => auth()->user()->id,
                        'relation_type_id' => $supervise_type->id,
                        'model_type' => Segment::class
                ]);

                foreach ($resources as $resource) {
                    $data_supervisor[] = [
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
