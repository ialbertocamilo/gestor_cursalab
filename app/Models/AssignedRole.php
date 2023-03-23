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
    public static function getUserAssignedRoles($userId): object
    {

        return AssignedRole::query()
                           ->where('entity_type', self::USER_ENTITY)
                           ->where('entity_id', $userId)
                           ->get();
    }

    /**
     * Check whether user has specific role
     *
     * @param $userId
     * @param $roleId
     * @return bool
     */
    public static function hasRole($userId, $roleId): bool
    {

        $role = DB::table('assigned_roles')
                    ->where('entity_type', self::USER_ENTITY)
                    ->where('entity_id', $userId)
                    ->where('role_id', $roleId)
                    ->first();

        return (bool)$role;
    }

    /**
     * Load superusers ids from a specific workspace
     *
     * @param $workspaceId
     * @return mixed[]
     */
    public static function getSuperusersIds ($workspaceId) {
        $items =  AssignedRole::query()
            ->where('entity_type', self::USER_ENTITY)
            ->where('scope', $workspaceId)
            ->where('role_id', Role::SUPER_USER)
            ->get();

        return $items->pluck('entity_id')->toArray();
    }
}
