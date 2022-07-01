<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;

use App\Models\Role;
// use Silber\Bouncer\Database\Role as Rol;

use App\Models\Ability;
use App\Models\Taxonomy;

use App\Utils\ApiResponse;
use Bouncer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Resources\RoleResource;

class RoleController extends Controller
{
    public function index()
    {

    }

    public function search(Request $request)
    {
        $roles = Role::search($request);

        RoleResource::collection($roles);

        return $this->success($roles);
    }

    public function formData()
    {
        $data['permissions'] = Ability::getAbilititesForTree();

        return $this->success($data);
    }

    public function store(RoleRequest $request)
    {
        $data = $request->validated();
        $row = Role::create($data);

        Bouncer::allow($row->name)->to($data['permissions']);

        return $this->success([], 'Rol registrado correctamente.');
    }

    public function update(Role $role, RoleRequest $request)
    {
        $data = $request->validated();

        $role->update($data);

        $role->abilities()->sync($data['permissions']);

        return $this->success([], 'Rol actualizado correctamente.');
    }

    public function show(Role $role)
    {
        $data = $role->toArray();

        $data['groups'] = $role->getAbilities()->groupBy('entity_type');
        $data['permissions_ticked'] = $role->getAbilities()->pluck('id')->toArray();

        return $this->success($data);
    }

    public function remove(Role $role)
    {
        $response = $role->delete();

        return $this->success([], 'Rol eliminado correctamente.');
    }

    public function active(Role $role)
    {
        return $role->changeStatus();
    }
}
