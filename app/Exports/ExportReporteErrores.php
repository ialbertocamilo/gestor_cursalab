<?php

namespace App\Exports;

use App\Models\ErroresMasivo;

use App\Exports\ExportErrorEvas;
use App\Exports\ExportErrorTemas;
use App\Exports\ExportErrorCursos;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportReporteErrores implements WithMultipleSheets
{
    use Exportable;
    public $tipo;
    public function sheets(): array
    {
        $sheets = [];
        switch ($this->tipo) {
            case 'curs_tema_eva':
                $err_cursos = DB::table('err_masivos')->where('type_masivo','cursos')->get();
                $err_temas = DB::table('err_masivos')->where('type_masivo','temas')->get();
                $err_preguntas= DB::table('err_masivos')->where('type_masivo','preguntas')->get();
                array_push($sheets, new ExportErrorCursos($err_cursos));
                array_push($sheets, new ExportErrorTemas($err_temas));
                array_push($sheets, new ExportErrorEvas($err_preguntas));
            break;
            case 'usuarios':
                
            break;
        }
        return $sheets;
    }
}
