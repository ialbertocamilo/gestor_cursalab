<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Criterion extends BaseModel
{

    protected $table = 'criteria';

    protected $fillable = [
        'name', 'code', 'position',
        'parent_id', 'field_id', 'validation_id',
        'show_as_parent', 'show_in_reports', 'show_in_ranking',
        'show_in_profile', 'show_in_segmentation', 'is_default',
        'show_in_form', 'required', 'editable_configuration',
        'editable_segmentation', 'multiple', 'active', 'description',
    ];

    protected $casts = [
        'required' => 'boolean',
        'show_in_segmentation' => 'boolean',
    ];

    public function sluggable(): array
    {
        return [
            'code' => ['source' => 'name', 'onUpdate' => false, 'unique' => true]
        ];
    }

    public function values()
    {
        return $this->hasMany(CriterionValue::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function field_type()
    {
        return $this->belongsTo(Taxonomy::class, 'field_id');
    }

    protected function getValuesForSelect($criterion_code)
    {
        $current_workspace = get_current_workspace();

        $criterion = Criterion::with('field_type')->where('code', $criterion_code)->first();
        $column_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);

        return CriterionValue::query()
            ->whereRelation('criterion', 'code', $criterion_code)
            ->whereRelation('workspaces', 'id', $current_workspace?->id)
            ->where('active', ACTIVE)
            ->select('id', "$column_name as nombre")
            ->get();
    }



    protected function getValuesByCode($criterion_code)
    {
        $current_workspace = get_current_workspace();
        $criterion = Criterion::with('field_type')->where('code', $criterion_code)->first();
        $column_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);

        return CriterionValue::query()
            ->whereRelation('criterion', 'code', $criterion_code)
            ->select('id', 'value_text as nombre')
            ->where('active', ACTIVE)
            ->limit(5)->get();
    }

    protected function search($request)
    {
        $criterion_values_id = CriterionValue::whereRelation('workspaces', 'id', $request->workspace_id)->pluck('id')->toArray();
        $query = self::withCount([
            'values' => function ($q) use ($request, $criterion_values_id) {
                if ($request->workspace_id)
                    $q->whereIn('id', $criterion_values_id);
            }
        ]);

        if ($request->q)
            $query->where('name', 'like', "%$request->q%");

        if ($request->workspace_id)
            $query->whereRelation('workspaces', 'id', $request->workspace_id);

        $field = $request->sortBy ?? 'name';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->paginate);
    }

    protected function storeRequest($data, $model = null, $files = [])
    {
        try {

            DB::beginTransaction();

            if ($model) :

                $model->update($data);

            else:

                $model = self::create($data);

            endif;

            if ($data['workspace_id'])
                $model->workspaces()->syncWithoutDetaching([$data['workspace_id']]);


            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

    protected function validationsOnUpdate(Criterion $criterion, array $request): array
    {
        $validations = collect();

        $type_was_changed = $this->wasFieldIdChanged($criterion, $request);
        if ($type_was_changed['ok']) $validations->push($type_was_changed);

        $show_confirm = !($validations->where('show_confirm', false)->count() > 0);

        return [
            'list' => $validations->toArray(),
            'title' => !$show_confirm ? 'Alerta' : 'Tener en cuenta',
            'show_confirm' => $show_confirm
        ];
    }

    public function wasFieldIdChanged($criterion, $request): array
    {
        $temp['ok'] = $criterion->field_id != $request['field_id'];

        if (!$temp['ok']) return $temp;

        $temp['title'] = "No se puede cambiar el tipo de valor del criterio.";
        $temp['subtitle'] = "Para poder cambiar el tipo de valor del criterio es necesario ...";
        $temp['show_confirm'] = false;
        $temp['type'] = 'field_id_was_changed';

        return $temp;
    }
}
