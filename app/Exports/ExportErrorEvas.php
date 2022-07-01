<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportErrorEvas implements FromView, WithTitle
{
    private $errores=[]; 
    public function __construct($errores)
    {   
        $this->$errores = $errores;
    }
    public function view(): View
    {
       return view('exportar.reporte_error_evas', [
           'errores' => $this->errores
       ]);
    }


    public function title(): string
    {
        return 'Carga Evaluaciones';
    }
}
