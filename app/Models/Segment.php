<?php

namespace App\Models;

class Segment extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'criterion_value_count', 'active'
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function requirements()
    {
        return $this->hasMany(SegmentRequirement::class);
    }

    protected function search($request)
    {
        $query = self::with('segments');

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
