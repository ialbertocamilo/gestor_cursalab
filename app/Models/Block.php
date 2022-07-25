<?php

namespace App\Models;

class Block extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'criterion_value_count', 'active'
    ];

    public function segments()
    {
        return $this->belongsToMany(Segment::class);
    }

    public function block_segments()
    {
        return $this->hasMany(BlockSegment::class);
    }

    public function criterion_values()
    {
        return $this->belongsToMany(CriterionValue::class);
    }

    protected function search($request)
    {
        $query = self::with('segments', 'criterion_values.criterion')
                        ->withCount(['segments' => function($q) { $q->where('active', ACTIVE); }, 'criterion_values']);

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%$request->q%");
                $q->orWhere('email', 'like', "%$request->q%");
            });
        }

        if ($request->service)
            $query->where('service_id', $request->service);

        if ($request->type)
            $query->where('type_id', $request->type);

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

}
