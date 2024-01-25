<?php

namespace App\Models;

use App\Traits\CustomAudit;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class Taxonomy extends Model
{
    use SoftDeletes;
    use Cachable;
    // Cross Database support
    use HybridRelations;

    // protected $connection = 'mysql';
    // protected $rememberFor = WEEK_MINUTES;
    // use Cachable;
    use Cachable {
        Cachable::newEloquentBuilder insteadof HybridRelations;
    }
    protected $table = 'taxonomies';

    protected $fillable = [
        'external_id',
        'external_id_es',
        'group',
        'type',
        'position',
        'code',
        'name',
        'path',
        'alias',
        'icon',
        'color',
        'extra_attributes',
        'slug',
        'active',
        'description',
        'detail',
        'parent_id',
        'workspace_id',
        'external_parent_id',
        'external_parent_id_es',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'extra_attributes' => 'json',
    ];
    /*

        Relationships

    --------------------------------------------------------------------------*/

    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id')
                    ->with('parent');
    }

    public function children()
    {
        return $this->hasMany(Taxonomy::class, 'parent_id')
                    ->orderBy('position')
                    ->orderBy('name')
                    ->with('children');
        // return $this->hasMany(Taxonomy::class, 'parent_id')->orderBy('position');
    }

    public function features()
    {
        return $this->hasMany(Feature::class, 'section_id');
    }

    /*

        Attributes

    --------------------------------------------------------------------------*/

    public function setCodeAttribute($value = '')
    {
        if (!empty($value)) {
            $this->attributes['code'] = $value;
        }
    }

    /*

        Methods

    --------------------------------------------------------------------------*/

    /**
     * Load group's types from taxonomy
     *
     * @param string $groupName
     * @param string $typeName
     * @return mixed
     */
    protected function getDataForSelect(string $groupName, string $typeName)
    {
        return Taxonomy::where('type', $typeName)
            ->where('group', $groupName)
            ->where('active', 1)
            ->orderBy('name', 'ASC')
            ->get(['name', 'id', 'code', 'name as nombre', 'position']);
    }

    protected function getDataForSelectAttrs(string $groupName, string $typeName, array $attributes)
    {
        return Taxonomy::where('type', $typeName)
            ->where('group', $groupName)
            ->where('active', 1)
            ->orderBy('name', 'ASC')
            ->get($attributes);
    }

    protected function getDataForSelectWorkspace(string $groupName, string $typeName)
    {
        return Taxonomy::where('workspace_id', get_current_workspace()->id)
            ->where('group', $groupName)
            ->where('type', $typeName)
            ->where('active', 1)
            ->orderBy('name', 'ASC')
            ->get(['name', 'id', 'code', 'name as nombre']);
    }

    /**
     * Load Vademecum categories
     *
     * @param $workspaceId
     * @return Builder
     */
    public static function vademecumCategory($workspaceId): Builder
    {
        return Taxonomy::query()
            ->where('workspace_id', $workspaceId)
            ->where('group', 'vademecum')
            ->where('type', 'categoria')
            ->where('active', ACTIVE);
    }

    /**
     * Load Vademecum subcategories from specific category
     *
     * @param $workspaceId
     * @param $categoryId
     * @return Builder
     */
    public static function vademecumSubcategory($workspaceId, $categoryId): Builder
    {
        return Taxonomy::query()
            ->where('workspace_id', $workspaceId)
            ->where('group', 'vademecum')
            ->where('type', 'subcategoria')
            ->where('parent_id', $categoryId)
            ->where('active', ACTIVE);
    }

    /**
     * Load Videoteca tags
     *
     * @return Builder
     */
    protected function videotecaTags($workspaceId)
    {
        return Taxonomy::query()
                        ->where('workspace_id', $workspaceId)
                        ->where('group', 'videoteca')
                        ->where('type', 'tag')
                        ->where('active', ACTIVE);
    }

    /**
     * Load Videoteca categories
     *
     * @return Builder
     */
    protected function videotecaCategories($workspaceId) {

        return Taxonomy::query()
                        ->where('workspace_id', $workspaceId)
                        ->where('group', 'videoteca')
                        ->where('type', 'categoria')
                        ->where('active', ACTIVE);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
                'unique' => false
            ]
        ];
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            // ->width(350)
            // ->height(350)
            ->crop('crop-center', 350, 350)
            ->performOnCollections('avatar')
            ->keepOriginalImageFormat();

        $this
            ->addMediaConversion('main')
            ->crop('crop-center', 800, 800)
            // ->fit('contain', 1920, 1080)
            ->quality(85)
            ->withResponsiveImages()
            ->performOnCollections('avatar')
            ->keepOriginalImageFormat();
    }

    protected function getSelectData($group, $type, array $filters = [], array $extraFields = [], mixed $relationship = [])
    {
        $default = ['color', 'icon', 'name', 'id', 'code', 'position'];

        $fields = array_merge($default, $extraFields);

        return $this->getData($group, $type, $filters, $relationship)
                    ->get($fields)
                    ->toArray();
    }

    protected static function getData($group, $type, $filters = [], mixed $relationship = null)
    {
        $result = Taxonomy::where('group', $group)
                          ->where('type', $type)
                          ->where('active', ACTIVE);

        foreach ($filters as $field => $filter) {
            $statement = is_array($filter) ? 'whereIn' : 'where';

            $result->$statement($field, $filter);
        }

        $result = $result->orderBy('position', 'ASC');

        return $relationship ? $result->with($relationship) : $result;
    }

    protected function getSelectSectionData($group, $type, array $filters = [], array $extraFields = [], mixed $relationship = [])
    {
        $default = ['color', 'icon', 'name', 'id', 'code'];

        $fields = array_merge($default, $extraFields);

        return $this->getSectionData($group, $type, $filters, $relationship)
                    ->get($fields)
                    ->toArray();
    }

    protected function getSectionData($group, $type, $filters = [])
    {
        $q = Taxonomy::where('group', $group)
                     ->where('type', $type)
                     ->where('active', ACTIVE);

        foreach ($filters as $field => $filter) {
            $statement = is_array($filter) ? 'whereIn' : 'where';

            $q->$statement($field, $filter);
        }

        $q->whereNull('parent_id');

        return $q->orderBy('position', 'ASC');
    }

    protected function getSelectSubSectionData($group, $type, array $filters = [], array $extraFields = [], mixed $relationship = [])
    {
        $default = ['color', 'icon', 'name', 'id', 'code'];

        $fields = array_merge($default, $extraFields);

        return $this->getSubSectionData($group, $type, $filters, $relationship)
                    ->get($fields)
                    ->toArray();
    }

    protected function getSubSectionData($group, $type, $filters = [])
    {
        $q = Taxonomy::where('group', $group)
                     ->where('type', $type)
                     ->where('active', ACTIVE);

        foreach ($filters as $field => $filter) {
            $statement = is_array($filter) ? 'whereIn' : 'where';

            $q->$statement($field, $filter);
        }

        // $q->whereNull('parent_id');

        return $q->orderBy('position', 'ASC');
    }

    /**
     * Load types from taxonomy filtered with its parent id
     *
     * @param $parent_id
     * @param $group
     * @param $type
     * @param array $selectParameters
     * @return mixed
     */
    protected function getSelectChildrenData(
        $parent_id,
        $group,
        $type,
        array $selectParameters = ['id', 'name', 'name as nombre']
    )
    {
        return $this->getChildrenData($parent_id, $group, $type)
                    ->select($selectParameters)
                    ->get()
                    ->toArray();
    }

    public function getChildrenData($parent_id, $group, $type)
    {
        return Taxonomy::where('group', $group)->where('type', $type)
                          ->where('active', ACTIVE)
                          ->where('parent_id', $parent_id)
                          ->orderBy('position', 'ASC');
    }

    protected function getFirstData($group, $type, $code)
    {
        $data = $this->getData($group, $type)->get();

        return $data->where('code', $code)->first();
    }

    protected function getFirstOrCreate($group, $type, $value, $parent_id = NULL)
    {
        $value = trim($value);

        if ($value) {
            $taxonomy = Taxonomy::firstOrCreate(
                ['parent_id' => $parent_id, 'group' => $group, 'type' => $type, 'name' => $value],
                ['parent_id' => $parent_id, 'group' => $group, 'type' => $type, 'name' => $value, 'active' => ACTIVE, 'code' => Str::slug($value, '-')]
            );

            return $taxonomy;
        }

        // Cache::flush();

        return NULL;
    }

    protected function getOrCreate($group, $type, $name)
    {
        $name = trim($name);

        $taxonomy = Taxonomy::where('group', $group)
                             ->where('type', $type)
                             ->where('name', $name)
                             ->first();

        if ( ! $taxonomy ) :

            $data = [
                'group' => $group,
                'type' => $type,
                'name' => $name,
                'active' => 1,
            ];

            $taxonomy = Taxonomy::create($data);
        endif;

        return $taxonomy;
    }

    protected function getOrCreateWithWorkspace($group, $type, $name)
    {
        $name = trim($name);

        $taxonomy = Taxonomy::where('group', $group)
                             ->where('workspace_id', get_current_workspace()->id)
                             ->where('type', $type)
                             ->where('name', $name)
                             ->first();

        if ( ! $taxonomy ) :

            $data = [
                'workspace_id' => get_current_workspace()->id,
                'group' => $group,
                'type' => $type,
                'name' => $name,
                'active' => 1,
            ];

            $taxonomy = Taxonomy::create($data);
        endif;

        return $taxonomy;
    }

    protected function getDataByGroupAndType($group, $type)
    {
        return Taxonomy::where('type', $type)
                       ->where('group', $group)
                       ->where('active', 1)
                       ->orderBy('name', 'ASC')
                       ->get();
    }

    protected function searchCharacters($request, $method = 'paginate')
    {
        $query = self::where(['group' => 'game', 'type' => 'character']);

        if ($request->filters) {
            foreach ($request->filters as $key => $filter) {
                if ($key == 'q') {
                    $query->where('name', 'like', "%{$request->filters['q']}%");
                }
            }
        }

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->descending == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->rowsPerPage);
    }
}
