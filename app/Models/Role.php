<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Silber\Bouncer\Database\Concerns\IsRole;

class Role extends BaseModel
{
    use IsRole;

    // Role ids

    public const SUPER_USER = 1;
    public const CONFIG = 2;
    public const ADMIN = 3;
    public const CONTENT_MANAGER = 4;
    public const TRAINER = 5;
    public const REPORTS = 6;
    public const USER = 7;


    public $defaultRelationships = [];
    // protected $ledgerThreshold = 100;
    protected $morphClass = 'MorphOrder';
    protected $fillable = ['name', 'title', 'level', 'scope', 'active', 'description'];

    // protected $with = ['children', 'parent'];
    // protected $with = ['description'];

    protected $hidden = ['pivot'];

    public function sluggable(): array
    {
        return [
            'name' => ['source' => 'title', 'onUpdate' => false, 'unique' => true]
        ];
    }

    public function getMorphClass()
    {
        return Role::class;
    }

    public function loadDefaultRelationships()
    {
        $relationships = array_values($this->defaultRelationships);

        return $this->load($relationships);
    }

    // public function getFullnameAttribute()
    // {
    //     if ($this->lastname)
    //         return $this->name . ' ' . $this->lastname;

    //     return $this->name;
    // }

    public function scopeWhereLike($query, $column, $value)
    {
        return $query->where($column, 'LIKE', "%$value%");

    }

    protected function search($request)
    {
        $query = self::whereNotIn('name', ['developer', 'superadmin']);

        if ($request->filters)
        {
            foreach($request->filters AS $key => $filter)
            {
                if ($key == 'q')
                    $query->where(function($q) use ($filter){
                        $q->where('name', 'like', "%$filter%");
                        $q->orWhere('title', 'like', "%$filter%");
                    });

                if ($key == 'starts_at')
                    $query->whereDate('created_at', '>=', $filter);

                if ($key == 'ends_at')
                    $query->whereDate('created_at', '<=', $filter);
            }
        }

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->descending == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->rowsPerPage);
    }
}
