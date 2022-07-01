<?php

namespace App\Http\Controllers;

use App\Models\EntrenadorUsuario;
use App\Http\Resources\Entrenador\EntrenadorSearchResource;
use App\Models\Usuario;
use Illuminate\Http\Request;

class EntrenadorController extends Controller
{
    public function search(Request $request)
    {
        $entrenadores = EntrenadorUsuario::searchEntrenadores($request);

        EntrenadorSearchResource::collection($entrenadores);
        return $this->success($entrenadores);
    }

    public function alumnos(Usuario $entrenador)
    {
        $entrenador = EntrenadorUsuario::alumnos($entrenador);

        return $this->success(compact('entrenador'));
    }
}
