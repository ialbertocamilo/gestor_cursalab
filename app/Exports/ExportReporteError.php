<?php

namespace App\Exports;

use App\Models\Usuario;
use App\Models\ErroresMasivo;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportReporteError implements FromView
{
    public $temas_n_r,$temas_o_r,$tipo;
    public function view(): View
    {
        $errs  = DB::table('err_masivos')->where('type_masivo',$this->tipo)->get();
        foreach ($errs as $err) {
            $err->err_data_original = json_decode($err->err_data_original,true);
        }
        $err_masivos =new ErroresMasivo();
        $headers =$err_masivos->get_header($this->tipo);
        unset($headers[count($headers)-1]);
        return view('exportar.reporte_error_masivos', [
            'errores'=>$errs,
            'headers' => $headers
        ]);
    }
}