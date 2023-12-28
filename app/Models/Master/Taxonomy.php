<?php

namespace App\Models\Master;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
// use Watson\Rememberable\Rememberable;

class Taxonomy extends Model
{
    // use Rememberable;

    protected $connection = 'mysql_master';
    // protected $rememberFor = 43200;

    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id')->with('parent');
    }

    public function children()
    {
        return $this->hasMany(Taxonomy::class, 'parent_id')->orderBy('position')->orderBy('name')->with('children');
    }
}
