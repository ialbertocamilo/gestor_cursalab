<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErrorAppRequest;
use App\Http\Requests\ErrorStatusRequest;
use App\Http\Resources\ErrorResource;
use App\Models\Error;
use App\Models\Taxonomy;
use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function search(Request $request)
    {
        $errors = Error::search($request);

        ErrorResource::collection($errors);

        return $this->success($errors);
    }

    public function getListSelects()
    {
        $platforms = Taxonomy::getDataForSelect('system', 'platform');
        $statuses = Taxonomy::getDataForSelect('error', 'status');

        return $this->success(get_defined_vars());
    }

    public function create()
    {
        // return $this->success(get_defined_vars());
    }

    public function getFormSelects()
    {
        return $this->success(get_defined_vars());
    }

    public function store(ErrorRequest $request)
    {
        $data = $request->validated();

        $error = Error::create($data);

        $msg = 'Error creado correctamente.';

        return $this->success(compact('msg'));
    }

    public function edit(Error $error)
    {
        $error->load('platform', 'user', 'usuario', 'status');

        $platforms = Taxonomy::getDataForSelect('system', 'platform');
        $statuses = Taxonomy::getDataForSelect('error', 'status');

        return $this->success(get_defined_vars());
    }

    public function update(Error $error, ErrorStatusRequest $request)
    {
        $data = $request->validated();

        $error->update($data);

        $msg = 'Error actualizado correctamente.';

        return $this->success(compact('msg'));
    }

    // public function status(Error $error, Request $request)
    // {
    //     $error->update(['estado' => !$error->estado]);

    //     return $this->success(['msg' => 'Estado actualizado correctamente.']);
    // }

    public function destroy(Error $error)
    {
        $error->delete();

        return $this->success(['msg' => 'Error eliminado correctamente.']);
    }
}
