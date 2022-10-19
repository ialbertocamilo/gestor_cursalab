<?php

namespace App\Http\Controllers\ApiRest;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdjuntarArchivosController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, ['token' => 'required']);

        $data = array();
        return view('adjuntar_archivos.index', compact('data'));
    }
}
