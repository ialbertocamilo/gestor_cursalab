<?php

namespace App\Models;

class Block extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'parent', 'criterion_value_count', 'active', 'position', 'mandatory'
    ];

    public function children()
    {
        return $this->belongsToMany(Block::class, 'blocks_children', 'block_id', 'block_child_id');
    }

    public function block_children()
    {
        return $this->hasMany(BlockChild::class, 'block_id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
        // return $this->belongsToMany(CriterionValue::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'blocks_courses');
    }

    // public function segments()
    // {
    //     return $this->belongsToMany(Segment::class);
    // }

    // public function block_segments()
    // {
    //     return $this->hasMany(BlockSegment::class);
    // }

    // public function criterion_values()
    // {
    //     return $this->belongsToMany(CriterionValue::class);
    // }

    protected function search($request)
    {
        // $query = self::with(['block_segments' => ['criterion_values.criterion', 'segment.courses'], 'criterion_values.criterion'])
        //                 ->withCount(['segments' => function($q) { $q->where('active', ACTIVE); }, 'criterion_values']);
        $query = self::with(['block_children' => ['segments.values.criterion', 'child' => ['courses', 'segments.values.criterion_value.criterion']]])
                        ->withCount(['children' => function($q) { $q->where('active', ACTIVE); }, 'segments']);

        $query->where('parent', 1);

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%$request->q%");
                // $q->orWhere('email', 'like', "%$request->q%");
            });
        }

        // if ($request->service)
        //     $query->where('service_id', $request->service);

        // if ($request->type)
        //     $query->where('type_id', $request->type);

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->paginate);
    }

}
