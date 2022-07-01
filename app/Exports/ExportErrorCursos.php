<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportErrorCursos implements FromView, WithTitle
{
    private $errores=[]; 
    public function __construct($errores)
    {   
        $this->$errores = $errores;
    }
    public function view(): View
    {
       return view('exportar.reporte_error_cursos', [
           'errores' => $this->errores
       ]);
    }


    public function title(): string
    {
        return 'Carga de Cursos';
    }
}
