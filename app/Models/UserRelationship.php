<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class UserRelationship extends BaseModel
{
    protected $table = 'user_relationships';


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
        $q = self::query();

        if ($request->code)
            $q->whereRelation('type', 'code', $request->code);

        $q->withWhereHas('supervisor', function ($query) use ($request) {
            if ($request->subworkspace)
                $query->whereRelation('subworkspace', 'id', $request->subworkspace);
            else
                $query->whereRelation('subworkspace.parent', 'id', $request->workspace);
        });


        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);

    }

    protected function createRelationAs($code, $users, $model_type, $model_id)
    {
        $temp = [];
        $relation_type = Taxonomy::getFirstData('user', 'action', $code);
        $now = now()->format('Y-m-d H:i:s');

        foreach ($users as $user) {
            $temp = [
                'user_id' => $user['id'] ?? $user->id,
                'relation_type_id' => $relation_type->id,
                'model_type' => $model_type,
                'model_id' => $model_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('user_relationships')->insert($temp);
    }


}
