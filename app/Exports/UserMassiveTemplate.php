<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\UsuarioController;
use Maatwebsite\Excel\Concerns\FromCollection;

class UserMassiveTemplate implements FromView
{
    public function view(): View
    {
        $usuario  = new UsuarioController();
        $formSelect = $usuario->getFormSelects(true);
        $criteria = $formSelect['criteria'];
        $has_DC3_functionality = $formSelect['has_DC3_functionality'];
        return view('masivo.user_massive_template',compact('criteria','has_DC3_functionality'));
    }
}
