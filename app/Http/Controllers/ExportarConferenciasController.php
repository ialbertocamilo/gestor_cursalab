<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExportarConferenciasController extends Controller
{
    public function index()
    {
        return view('exportar_conferencias.index');
    }
}
