<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CriterionValue extends BaseModel
{
    protected $fillable = [
        'external_id', 'criterion_id', 'parent_id', 'exclusive_criterion_id',
        'value_text', 'value_date', 'value_datetime', 'value_boolean', 'value_decimal', 'value_integer',
        'position', 'active', 'description',
    ];

    // protected $visible = ['id', 'criterion_id', 'parent_id',
    //     'value_text', 'value_date', 'value_datetime', 'value_boolean', 'value_decimal', 'value_integer',
    //     'position', ];

    protected $hidden = ['exclusive_criterion_id', 'description', 'active', 'created_at', 'updated_at', 'deleted_at'];

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }

    public function parents()
    {
        return $this->belongsToMany(CriterionValue::class, 'criterion_value_relationship', 'criterion_value_id', 'criterion_value_parent_id');
    }

    public function criterion_workspace()
    {
        return $this->belongsToMany(CriterionWorkspace::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function subworkspace()
    {
        return $this->hasOne(Workspace::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    protected function getListForSelect($criterion_code = null, $criterion_id = null)
    {
        $value_param = 'value_text';

        return self::query()
            ->select('id', "$value_param as name", 'parent_id')
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

    protected function getListForSelectValues($criterion_id, $workspace_id) {
        $criteria = self::query();

        return $criteria->with('parents:id,criterion_id,value_text')
                        ->whereHas('workspaces', function ($q) use ($workspace_id) {
                            $q->where('id', $workspace_id);
                        })
                        ->where('criterion_id', $criterion_id)->get();
    }

    protected function search($request = null)
    {
        $q = self::with('criterion.field_type')
                ->withCount(['users' => function($q) use ($request){
                    $q->whereRelation('subworkspace', 'parent_id', $request->workspace_id);
                }]);

        if ($request->code)
            $q->whereHas('criterion', fn($q) => $q->where('code', $request->code));

        if ($request->criterion_id)
            $q->where('criterion_id', $request->criterion_id);

        if ($request->workspace_id)
            $q->whereRelation('workspaces', 'id', $request->workspace_id);

        if (isset($request->q) && $request->q != '')
            $q->where('value_text', 'like', '%'.trim($request->q).'%');
        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);
        if($request->all){
            return $q->get();
        }
        return $q->paginate($request->paginate);
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

            if ($data['workspace_id'] ?? false)
                $model->workspaces()->syncWithoutDetaching([$data['workspace_id']]);

            DB::commit();

            return $model;
        } catch (\Exception $e) {
//            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

    protected function getColumnName($match): string
    {
        return match ($match) {
            'date' => 'value_date',
            'number' => 'value_integer',
            'boolean' => 'value_boolean',
            default => 'value_text',
        };
    }

    protected function getCriterionValueColumnNameByCriterion(Criterion $criterion = null): string
    {
      return self::getColumnName($criterion->field_type->code);
    }

    public function getCriterionValueColumnName(): string
    {
        return self::getColumnName($this->criterion->field_type->code ?? 'xx');
    }

    /**
     * Bulk insert record and get ids of all inserted records
     *
     * @param $data
     * @return array
     */
    public static function bulkInsertAndGetIds($data): array
    {
        $ids = [];
        DB::transaction(function() use ($data, &$ids) {

            CriterionValue::insert($data);
            $lastId = CriterionValue::orderByDesc('id')->first()->id;
            $ids = range($lastId - count($data) + 1, $lastId);
        });

        return $ids;
    }

    /**
     * Get all workspace's criteria
     *
     * @param $workspaceId
     * @return Builder[]|Collection
     */
    public static function getCriteriaFromWorkspace($workspaceId)
    {
        $criteria = Criterion::whereRelation('workspaces', 'workspace_id', $workspaceId)
                ->get()->toArray();

        return $criteria;
    }

    /**
     * Find those users who have no complete set of criteria
     * @param $workspaceId
     * @param $criteriaIds
     * @return array
     */
    public static function findUsersWithIncompleteCriteriaValues($workspaceId, $criteriaIds): array
    {
        $subworkspacesIds = Workspace::loadSubWorkspacesIds($workspaceId)->toArray();

        if (!$subworkspacesIds) {
            return [];
        }

        $query_criteria = $criteriaIds ? ' and cv.criterion_id in ('.implode(',', $criteriaIds).')' : '';

        $query = '
            select
                user_id,
                sum(criteria_count) total_criteria_count
            from (
                select
                    u.id user_id,
                    -- when a user has the same criterion with
                    -- different values, only counts as one
                    if (count(cv.criterion_id) >= 1, 1, 0) criteria_count
                from
                    users u
                    join criterion_value_user cvu on u.id = cvu.user_id
                    join criterion_values cv on cv.id = cvu.criterion_value_id
                where
                    u.active = 1
                    and cv.active = 1
                    and u.deleted_at is null
                    and cv.deleted_at is null
                    and u.subworkspace_id in ('.implode(',', $subworkspacesIds).')' .
                    $query_criteria .
                'group by
                  u.id, cv.criterion_id
            ) user_criteria_count
            group by user_id
            having total_criteria_count < :criteriaCount';

        return DB::select(DB::raw($query), ['criteriaCount' => count($criteriaIds)]);
    }


}
