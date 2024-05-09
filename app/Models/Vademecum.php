<?php

namespace App\Models;

use App\Imports\VademecumImport;
use App\Services\FileService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Vademecum extends Model
{

    use softdeletes;

    protected $table = 'vademecum';

    protected $fillable = [
        'id','name', 'category_id', 'subcategory_id', 'media_id', 'active', 'media_type'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * Generate audits tags
     * @return string[]
     */
    public function generateTags(): array
    {
        return [
            'modelo_independiente',
        ];
    }

    /*

        Relationships

    --------------------------------------------------------------------------*/

    public function modules()
    {
        return $this->belongsToMany(
            CriterionValue::class,
            'vademecum_module',
            'vademecum_id',
            'module_id'
        );
    }

    public function category()
    {
        return $this->belongsTo(Taxonomy::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Taxonomy::class, 'subcategory_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function actions()
    {
        return $this->morphMany(UserAction::class, 'model');
    }

    /*

        Attributes

    --------------------------------------------------------------------------*/

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = (
            $value==='true' OR
            $value === true OR
            $value === 1 OR
            $value === '1'
        );
    }

    /*

        Methods

    --------------------------------------------------------------------------*/


    public function incrementAction($type_id, $quantity = 1)
    {
        $user_id = auth()->user()->id;

        $row = $this->actions()
                    ->firstOrCreate(
                        compact('user_id', 'type_id')
                    );

        if ($row->score === null) {
            $row->score = 0;
            $row->save();
        }

        $row->score += $quantity;
        $row->save();

        return $row;
    }

    /**
     * Load filtered paginated records from database
     *
     * @param $request
     * @param bool $api
     * @param int $paginate
     * @return LengthAwarePaginator
     */
    protected function search($request, bool $api = false, int $paginate = 20)
    {
        $relationships = ['modules', 'category', 'subcategory', 'media'];

        if ($api) {

            $relationships = [
                'modules' => function ($q) use ($request) {
                    if ($request->module_id) {
                        // $workspace = Workspace::find($request->module_id);
                        $q->where('module_id', $request->module_id);
                        // $q->where('module_id', $workspace->criterion_value_id);
                    }
                 },
                'subcategory',
                'category',
                'media',
            ];
        }

        $query = Vademecum::with($relationships);

        if ($request->category_id)
            $query->where('category_id', $request->category_id);

        if ($request->subcategory_id) {

            $query->whereIn(
                'subcategory_id',
                explode(',', $request->subcategory_id)
            );
        }

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        if ($request->module_id) {

            // Filter vademecum with specific module

            $query->whereHas('modules', function ($q) use ($request) {
                $q->where('module_id', $request->module_id);
            });

        } else {

            // Filter vademecum from modules of the current workspace

            $modulesIds = Workspace::loadSubWorkspaces(['criterion_value_id'])
                ->pluck('criterion_value_id');

            $query->whereHas('modules', function ($q) use ($modulesIds) {
                $q->whereIn('module_id', $modulesIds);
            });
        }

        if ($request->active)
            $query->where('active', $request->active);

        if ($api) {

            $query->orderBy('name', 'ASC');

        } else {

            $field = $request->sortBy ?? 'name';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

            $query->orderBy($field, $sort);
        }

        return $query->paginate($paginate);
    }

    protected function searchCategories($request, $api = false, $paginate = 20)
    {
        $query = Vademecum::query();

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        $categorias = $query->paginate($paginate);

        return $categorias;
    }

    protected function storeRequest($data, $item = null)
    {
        try {

            DB::beginTransaction();
            $registerNotifications = false;

            if ($item) {

                $item->update($data);
                $message = 'Registro actualizado correctamente';

            } else {

                $item = $this->create($data);
                $message = 'Registro creado correctamente';

                $registerNotifications = true;
            }

            $item->modules()->sync($data['modules']);


            if ($registerNotifications) {
                self::registerNotificationsForDocument($item->id);
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            info($e);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'success', 'message' => $message];
    }

    /**
     * Register notifications for all active users in vademecum's modules
     * @param $vademecumId
     * @return void
     */
    public static function registerNotificationsForDocument($vademecumId): void
    {

        // Load vademecum's subworkspaces ids

        $subworkspacesIds = DB::select(DB::raw('
                select w.id
                from vademecum_module vm
                    join workspaces w on w.criterion_value_id = vm.module_id
                where vm.vademecum_id = :vademecumId
            '),['vademecumId' => $vademecumId]
        );

        $subworkspacesIds = collect($subworkspacesIds)
            ->pluck('id')
            ->toArray();

        // Load users ids from subworkspaces

        $usersIds = User::query()
            ->whereIn('subworkspace_id', $subworkspacesIds)
            ->where('active', 1)
            ->select('id')
            ->pluck('id')
            ->toArray();

        // Register notifications
        if(count($usersIds)){
            UserNotification::createNotifications(
                get_current_workspace()->id,
                $usersIds,
                UserNotification::NEW_DOCUMENT,
                [ ],
                'vademecum'
            );
        }
    }

    public function prepareTaxonomy($row, $key, $type)
    {
        $taxonomia = NULL;

        if (!empty($row[$key])) :

            $value = $row[$key];

            $taxonomia = $this->getOrCreateTaxonomyByValue($type, $value);

        endif;

        return $taxonomia;
    }

    public function getOrCreateTaxonomyByValue($type, $value)
    {
        $value = trim($value);

        if (is_numeric($value)) {

            return Taxonomy::where('group', 'vademecum')
                            ->where('type', $type)
                            ->where('id', $value)
                            ->first();
        }

        return Taxonomy::getOrCreate('vademecum', $type, $value);
    }

    protected function prepareSearchedData($elementos)
    {
        $result = $elementos->toArray();

        $data = [];

        foreach ($result['data'] as $key => $row) {

            // In previous versions, the only allowed type was SCORM,
            // because of that those records has a media but its
            // media type is null

            $mediaType = $row['media_type'] ?? null;
            if ($mediaType == null && $row['media'] != null) {
                $mediaType = 'scorm';
            }

            $fileUrl = $row['media']['file'] ?? null;
            if ($mediaType != 'scorm' && isset($row['media']['file'])) {
                $fileUrl = FileService::generateUrl($row['media']['file']);
            }

            $data[$key]['id'] = $row['id'];
            $data[$key]['name'] = $row['name'];
            $data[$key]['file'] = $fileUrl;
            $data[$key]['scorm'] = $row['media']['file'] ?? null;
            $data[$key]['media_type'] = $mediaType;
            $data[$key]['category'] = $row['category']['name'] ?? null;
            $data[$key]['category_id'] = $row['category_id'];
            $data[$key]['subcategory_id'] = $row['subcategory_id'];
            $data[$key]['active'] = $row['active'];
        }

        $result['data'] = $data;

        return $result;
    }
    protected function duplicateVademecumsFromWorkspace($current_modules_ids,$workspace,$modules_ids){
        $vademecums = Vademecum::select('id','name', 'category_id', 'subcategory_id', 'media_id', 'active', 'media_type')
        ->with(['category:id,group,type,name,active','subcategory:id,parent_id,group,type,name,active'])
        ->whereHas('modules', function ($q) use ($current_modules_ids) {
            $q->whereIn('module_id', $current_modules_ids);
        })->get();
        foreach ($vademecums as $vademecum) {
            $category = $vademecum->category->toArray();
            if ($vademecum->subcategory) {
                $subcategory = $vademecum->subcategory->toArray();
            }

            $vademecum = $vademecum->toArray();
            $_category = New Taxonomy();
            unset($category->id);
            $category['workspace_id'] = $workspace->id;
            $_category->fill($category);
            $_category->save();

            if ($subcategory) {
                $_subcategory = new Taxonomy();
                unset($subcategory['id']);
                $subcategory['workspace_id'] = $workspace->id;
                $subcategory['parent_id'] = $_category->id;
                $_subcategory->fill($subcategory);
                $_subcategory->save();
            }

            $_vademecum = new Vademecum();
            unset($vademecum['id']);
            $vademecum['category_id'] = $_category->id;
            $vademecum['subcategory_id'] = $_subcategory->id;
            $_vademecum->fill($vademecum);
            $_vademecum->save();
            $_vademecum->modules()->sync($modules_ids);
        }
    }
    // todo: this method, is not used anywhere
    // protected function importFromFile($data)
    // {
    //     try {
    //         $model = (new VademecumImport);

    //         $model->modulo_id = $data['modulo_id'];
    //         $model->category_id = $data['category_id'];

    //         $model->import($data['excel']);

    //         if ($model->failures()->count()) {
    //             request()->session()->flash('excel-errors', $model->failures());

    //             return ['status' => 'error', 'message' => 'Se encontraron algunos errores.'];
    //         }
    //     } catch (\Exception $e) {
    //         // info($e);
    //         return ['status' => 'error', 'message' => $e->getMessage()];
    //     }

    //     return ['status' => 'success', 'message' => 'Registros ingresados correctamente.'];
    // }
}
