<?php

namespace App\Exports;

use App\Models\Eventos;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EventosExport implements FromView
{

    private $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $eventos = Eventos::with([
                    'estado' => function($query){
                        $query->select('estado_evento.estado', 'id');
                    },
                ])
                ->withCount('invitados')
                ->where('usuario_id', $this->filtros['creador_id'])
                ->get(['titulo', 'id', 'tipo_evento_id', 'descripcion', 'estado_id', 'fecha_inicio', 'hora_inicio_real', 'hora_fin_real', 'fecha_fin', 'duracion']);
        return view('exportar.exportar_eventos',[
            'data'   => $eventos,
        ]);
    }
}
