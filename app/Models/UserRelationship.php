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

        return $data;
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

}
