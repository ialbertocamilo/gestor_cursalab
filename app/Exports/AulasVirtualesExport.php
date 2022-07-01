<?php

namespace App\Exports;

use App\Models\Eventos;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AulasVirtualesExport implements FromView
{
    private $estados;
    private $master;

    public function __construct($estados, $master)
    {
        $this->estados = $estados;
        $this->master = $master;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        // $last_month = Carbon::now()->addMonths(-3)->format('Y-m-d');
        // $last_month = Carbon::now()->firstOfMonth()->format('Y-m-d');

        $eventos = Eventos::with(['estado', 'tipo_evento', 'cuentaZoom','creador'=>function($q){
                                $q->select('id','dni','nombre');
                            }])
                            ->withCount(['invitados', 'actividad_invitados'])
                            // ->where('fecha_inicio', '>=', $last_month)
                            ->whereIn('estado_id', $this->estados)
                            ->orderBy('fecha_inicio')
                            ->get();
        $is_master = $this->master;
        return view('exportar.exportar_aulas_virtuales', compact('eventos', 'is_master'));
    }
}
