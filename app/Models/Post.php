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
        'active'
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

    /*

        Methods

    --------------------------------------------------------------------------*/


    /**
     * Load filtered paginated records from databa
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    protected function search($request)
    {
        $query = self::query();

        if ($request->q) {
            $query->where('title', 'like', "%$request->q%");
        }

        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }
}
