<?php

namespace App\Exports;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportReporteBD2019 implements FromView
{
    public $temas_n_r,$temas_o_r;
    public function view(): View
    {
        $q_t_n = count($this->temas_n_r);
        $q_t_o = count($this->temas_o_r);
        $array_mayor =  ($q_t_n > $q_t_o) ? $q_t_n : $q_t_o ; 
        return view('exportar.reporte_usu_BD2019', [
            'temas_n_r'=>$this->temas_n_r,
            'temas_o_r' => $this->temas_o_r,
            'array_mayor' =>$array_mayor,
        ]);
    }
}
