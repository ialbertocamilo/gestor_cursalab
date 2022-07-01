<?php

namespace App\Http\Controllers;

use App\Models\Zoom;
use App\Models\CuentaZoom;

use Illuminate\Http\Request;
use App\Http\Requests\CuentaZoomRequest;
use App\Http\Resources\CuentaZoomResource;
// use App\Http\Controllers\ZoomApi;

class CuentasZoomController extends Controller
{
    public function search(Request $request)
    {
        // $x = 8/0;
        // CentuaZoom::search();
        $cuentas = CuentaZoom::search($request);

        CuentaZoomResource::collection($cuentas);

        return $this->success($cuentas);
    }

    public function create()
    {
        $tipos = config('data.cuentas-zoom-tipos');

        return $this->success(get_defined_vars());
    }

    public function store(CuentaZoomRequest $request)
    {
        $cuenta = CuentaZoom::create($request->validated());

        return $this->success(['msg' => 'Cuenta creada correctamente.']);
    }

    public function edit(CuentaZoom $cuenta)
    {
        $tipos = config('data.cuentas-zoom-tipos');

        return $this->success(get_defined_vars());
    }

    public function update(CuentaZoom $cuenta, CuentaZoomRequest $request)
    {
        $cuenta->update($request->validated());

        return $this->success(['msg' => 'Cuenta editada correctamente.']);
    }

    public function status(CuentaZoom $cuenta, Request $request)
    {
        $cuenta->update(['estado' => !$cuenta->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function generarToken(CuentaZoom $cuenta, Request $request)
    {
        $cuenta->generateJWT();
        $cuenta->regenerateZoomTokens();

        return $this->success(['msg' => 'Tokens SDK y ZAK generados correctamente.']);
    }

    public function destroy(CuentaZoom $cuenta)
    {
        $cuenta->delete();

        return $this->success(['msg' => 'Cuenta eliminada correctamente.']);
    }
}
