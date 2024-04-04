<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Post extends BaseModel
{

    protected $fillable = [
        'code',
        'title',
        'title_short',
        'subtitle',
        'slug',
        'content',
        'description',
        'position',
        'platform_id',
        'section_id',
        'category_id',
        'user_id',
        'active',
        'check_text_area',
        'platform_id_onb'
    ];

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
    public function setCheckTextAreaAttribute($value)
    {
        $this->attributes['check_text_area'] = (
            $value==='true' OR
            $value === true OR
            $value === 1 OR
            $value === '1'
        );
    }

    /*

        Scopes

    --------------------------------------------------------------------------*/

    public function scopeFilterByPlatform($q){
        $platform = session('platform');
        $type_id = $platform && $platform == 'induccion'
                    ? Taxonomy::getFirstData('project', 'platform', 'onboarding')->id
                    : Taxonomy::getFirstData('project', 'platform', 'training')->id;
        $q->where('platform_id_onb',$type_id);
    }
    /*

        Methods

    --------------------------------------------------------------------------*/


    /**
     * Load filtered paginated records from database
     *
     * @param $request
     * @param null $section_id
     * @return LengthAwarePaginator
     */
    protected function search($request, $section_id = null)
    {
        if ($section_id) {
            $query = self::where('section_id', $section_id);
        } else {
            $query = self::query();
        }

        $query->FilterByPlatform();

        if ($request->q) {
            $query->where('title', 'like', "%$request->q%");
        }

        if ($request->active == 1)
            $query->where('active', ACTIVE);

        if ($request->active == 2)
            $query->where('active', '<>', ACTIVE);

        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
