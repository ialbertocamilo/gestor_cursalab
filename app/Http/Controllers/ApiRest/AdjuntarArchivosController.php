<?php

namespace App\Http\Controllers\ApiRest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;

class AdjuntarArchivosController extends Controller
{
    public function index(Request $request)
    {
        if(!$request->token) abort(403);

        $data = array();
        return view('adjuntar_archivos.index', compact('data'));
    }
}
