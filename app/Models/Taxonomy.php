<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Taxonomy extends Model
{
    use SoftDeletes;

    // protected $rememberFor = WEEK_MINUTES;

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
        'slug',
        'active',
        'description',
        'detail',
        'parent_id',
        'external_parent_id',
        'external_parent_id_es',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
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
        $default = ['color', 'icon', 'name', 'id', 'code'];

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

    protected function getSelectChildrenData(
        $parent_id, $group, $type, array $selectParameters = ['id', 'name']
    )
    {
        return $this->getChildrenData($parent_id, $group, $type)
                    ->select($selectParameters)
                    ->get()
                    ->toArray();
    }

    public function getChildrenData($parent_id, $group, $type)
    {
        $result = Taxonomy::where('group', $group)->where('type', $type)
                          ->where('active', ACTIVE)
                          ->where('parent_id', $parent_id)
                          ->orderBy('position', 'ASC');

        // return $relationship ? $result->with($relationship) : $result;
        return $result;
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

    protected function getDataByGroupAndType($group, $type)
    {
        return Taxonomy::where('type', $type)
                       ->where('group', $group)
                       ->where('active', 1)
                       ->orderBy('name', 'ASC')
                       ->get();
    }

    protected function getDataForSelect($group, $type)
    {
        return Taxonomy::where('type', $type)
                        ->where('group', $group)
                        ->where('active', 1)
                        ->orderBy('name', 'ASC')
                        ->get(['name', 'id']);
                    // ->pluck('name', 'id')
                    // ->toArray();
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
