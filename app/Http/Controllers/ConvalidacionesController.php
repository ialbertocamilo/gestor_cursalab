<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConvalidacionesController extends Controller
{
    public function index()
    {
        $data = array();
        return view('convalidaciones.index', $data );
    }
}
