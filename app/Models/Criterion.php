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
        'editable_segmentation', 'multiple', 'active', 'description','can_be_create','avaiable_in_personal_data_guest_form'
    ];

    protected $casts = [
        'multiple' => 'boolean',
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
        $custom_pivot_fields = array_keys(Workspace::CUSTOM_PIVOT_FIELDS);

        return $this->belongsToMany(Workspace::class)->withPivot($custom_pivot_fields);
    }

    public function field_type()
    {
        return $this->belongsTo(Taxonomy::class, 'field_id');
    }

    # modules by code
    protected function getValuesForSelect($criterion_code, $current = false)
    {
        $current_workspace = get_current_workspace();

        $criterion = Criterion::with('field_type')->where('code', $criterion_code)->first();
        $column_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);

        return CriterionValue::query()
            ->whereRelation('criterion', 'code', $criterion_code)
            ->whereRelation('workspaces', 'id', $current_workspace?->id)
            ->where('active', ACTIVE)
            ->when($current, function($q) {
                $q->whereIn('id', current_subworkspaces_id('criterion_value_id'));
            })
            ->select('id', "$column_name as nombre")
            ->get();
    }

    protected function getListForSelectWorskpace($workspace_id) {
        $query = self::whereRelation('workspaces', 'id', $workspace_id)
                     ->select('id', 'name', 'code', 'description');

        return $query->orderBy('created_at', 'desc')->get();
    }

    protected function search($request)
    {
        // $criterion_values_id = CriterionValue::whereRelation('workspaces', 'id', $request->workspace_id)->pluck('id')->toArray();

        $query = self::withCount([
            'values' => function ($q) use ($request) {
                if ($request->workspace_id)
                    $q->whereRelation('workspaces', 'id', $request->workspace_id);
                    // $q->whereIn('id', $criterion_values_id);
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

    protected function getSelectionCheckbox($workspace = null)
    {
        $custom_pivot_fields = Workspace::CUSTOM_PIVOT_FIELDS;
        $criterionWorkspace = $workspace ? $workspace->criterionWorkspace : NULL;

        $criteria = Criterion::with('field_type')->where('active', ACTIVE)->orderByDesc('is_default')->get();
        $criteria_workspace = [];

        foreach ($criteria as $key => $criterion) {

            $in_segments = SegmentValue::where('criterion_id', $criterion->id)->count();

            $current = $workspace ? $criterionWorkspace->where('id', $criterion->id)->first() : NULL;

            $criterion_available = $workspace ? ($current ? true : false) : true;
            $criterion_disabled = false;

            if ($criterion->code == 'module') {

                $criterion_available = true;
                $criterion_disabled = true;
            }

            $criteria_workspace[$key] = [
                'criterion_id' => $criterion->id,
                'code' => $criterion->code,
                'name' => $criterion->name,
                'available' => $criterion_available,
                'disabled' => $criterion_disabled,
            ];

            foreach ($custom_pivot_fields as $code => $row) {

                $field_disabled = false;

                $field_available = $workspace ? ($current ? $current->pivot->$code : false) : true;

                if ($criterion->code == 'module') {

                    $field_available = true;
                    $field_disabled = true;
                }

                $criteria_workspace[$key]['fields'][$code] = [
                    'code' => $code,
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'available' => $field_available,
                    'disabled' => $field_disabled,
                    'text' => $row['type'] == 'text' ? ($current->pivot->$code ?? $criterion->name) : NULL,
                ];
            }
        }

        return compact('criteria', 'criteria_workspace');
    }

    protected function setCriterionNameByCriterionTitle($criteria)
    {
        foreach ($criteria as $key => $criterion) {

            $workspace = $criterion->workspaces->first();

            $criterion->name = $workspace->pivot->criterion_title ?? $criterion->name;
        }

        return $criteria;
    }

    protected function overrideCriterionWorkspaceTitle($criterionWorkspace)
    {
        foreach ($criterionWorkspace as $criterion) {
            $criterion->name = $criterion->pivot->criterion_title ?? $criterion->name;
        }
    }

    public static function countUsersWithEmptyCriteria($workspaceId) {

        $criteriaIds = SegmentValue::loadWorkspaceSegmentationCriteriaIds($workspaceId);
        $users =  CriterionValue::findUsersWithIncompleteCriteriaValues($workspaceId, $criteriaIds);
        return count($users);
    }
}
