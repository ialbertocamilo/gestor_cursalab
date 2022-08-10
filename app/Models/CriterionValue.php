<?php

namespace App\Models;

class CriterionValue extends BaseModel
{
    protected $fillable = [
        'external_id', 'criterion_id', 'parent_id', 'exclusive_criterion_id',
        'value_text', 'value_date', 'value_datetime', 'value_boolean', 'value_decimal', 'value_integer',
        'position', 'active', 'description',
    ];

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }

    public function parents()
    {
        return $this->belongsToMany(CriterionValue::class, 'criterion_value_relationship', 'criterion_value_id', 'criterion_value_parent_id');
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    protected function getListForSelect($criterion_code = null, $criterion_id = null)
    {
        $value_param = 'value_text';

        return self::query()
            ->select('id', "$value_param as name")
            ->when($criterion_code ?? null,
                function ($q) use ($criterion_code) {
                    $q->whereHas('criterion',
                        fn($q) => $q->where('code', $criterion_code)
                    );
                })
            ->when($criterion_id ?? null,
                function ($q) use ($criterion_id) {
                    $q->whereHas('criterion',
                        fn($q) => $q->where('id', $criterion_id)
                    );
                })
            ->get();
    }

    protected function search($request = null)
    {
        $q = self::query();

        if ($request->code)
            $q->whereHas('criterion', fn($q) => $q->where('code', 'module'));


        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }
}
