<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Altek\Accountant\Models\Ledger;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jenssegers\Mongodb\Eloquent\Model;

use Carbon\Carbon;

class UserConfigData extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'user_config_data';

    protected $fillable = ['user_id', 'config_gestor', 'created_at', 'updated_at'];

    // protected $casts = [
    //     'current_courses_updated_at' => 'datetime',
    // ];

    // protected $primaryKey = "_id";

    // public $incrementing = false;
    // protected $with = ['children', 'parent'];
    // protected $excluded_fields = ['created_at', 'updated_at', 'id', 'password'];

    // relationships

    public function user(): ?MorphTo
    {
        return $this->morphTo();
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    // public function model()
    // {
    //     return $this->belongsTo(Taxonomy::class, 'recordable_type', 'path')
    //         ->where('group', 'system')
    //         ->where('type', 'model');
    // }

    // public function action_name()
    // {
    //     return $this->belongsTo(Taxonomy::class, 'event', 'code')
    //         ->where('group', 'system')
    //         ->where('type', 'action');
    // }

    // public function event_name()
    // {
    //     return $this->belongsTo(Taxonomy::class, 'event', 'code')
    //         ->where('group', 'system')
    //         ->where('type', 'event');
    // }


}
