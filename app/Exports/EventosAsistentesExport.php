<?php

namespace App\Exports;

use App\Models\Eventos;
use App\Models\AsistenteEvento;
use App\Models\ActvidadEvento;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EventosAsistentesExport implements FromView
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
        // dd($this->filtros['creador_id']);
        $eventos = Eventos::with([
                    'invitados' => function($query){
                        $query->select('asistentes_evento.usuario_id', 'asistentes_evento.primera_asistencia', 'asistentes_evento.segunda_asistencia', 'asistentes_evento.evento_id', 'asistentes_evento.id');
                    },
                    'invitados.asistente_data' => function($query){
                        $query->select('usuarios.nombre', 'usuarios.dni', 'usuarios.config_id', 'usuarios.id', 'usuarios.grupo' );
                    },
                    'invitados.asistente_data.criterio' => function($query){
                        $query->select('criterios.valor', 'criterios.id');
                    },
                    'invitados.asistente_data.config' => function($query){
                        $query->select('ab_config.etapa', 'ab_config.id');
                    },
                    'invitados.asistente_data.matricula_presente.carrera' => function($query){
                        $query->select('carreras.nombre', 'carreras.id');
                    },
                    'invitados.asistente_data.matricula_presente.ciclo' => function($query){
                        $query->select('ciclos.nombre', 'ciclos.id');
                    },
                    'actividad_invitados' => function($query){
                        $query->select('usuario_id', 'cant_ingresos', 'cant_salidas', 'id', 'evento_id');
                    },
                ])
                ->where('eventos.usuario_id', $this->filtros['creador_id'])
                ->get(['id', 'titulo', 'fecha_inicio']);
        return view('exportar.exportar_eventos_asistentes',[
            'data'   => $eventos,
        ]);
    }
}
