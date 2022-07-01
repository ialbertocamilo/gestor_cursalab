<?php

namespace App\Exports;

use App\Models\Eventos;
use App\Models\Usuario;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MigracionBoticasExport implements FromView
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $usuarios1 = Usuario::with('config', 'criterio')
                        ->whereIn('id', $this->data['no_coincide_botica_grupo'])
                        ->get();
        $usuarios2 = Usuario::with('config', 'criterio')
                        ->whereIn('id', $this->data['no_existe_tabla_botica'])
                        ->get();
        return view('exportar.migracion_boticas',[
            'usuarios1'   => $usuarios1,
            'usuarios2'   => $usuarios2
        ]);
    }
}
