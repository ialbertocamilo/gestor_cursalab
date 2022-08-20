<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class AssignedRole extends Model
{

    public const USER_ENTITY = 'App\Models\User';

    /**
     * Load assigned role from given user id
     *
     * @param $userId
     * @return object
     */
    public static function getUserAssignedRole($userId): object
    {

        return DB::table('assigned_roles')
            ->where('entity_type', self::USER_ENTITY)
            ->where('entity_id', $userId)
            ->first();
    }
}
