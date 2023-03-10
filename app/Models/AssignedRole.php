<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Load all workspaces' admin users
     *
     * @param $workspaceId
     * @return Builder[]|Collection
     */
    public static function loadAllAdmins($workspaceId): Collection|array
    {

        return User::query()
            ->join('assigned_roles as ar', 'ar.entity_id', 'users.id')
            ->where('ar.entity_type', self::USER_ENTITY)
            ->where('ar.scope', $workspaceId)
            ->where('ar.role_id', Role::ADMIN)
            ->where('users.active', 1)
            ->select('users.*')
            ->get();
    }
}
