<?php

namespace App\Console\Commands;

use App\Models\Eventos;
use Carbon\Carbon;
use App\Models\ActividadEvento;
use App\Models\AsistenteEvento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class primera_segunda_asistencia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventos:primera_segunda_asistencia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marcar primera y segunda asistencia';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info("----------------------------------------------------------------------------------------------------------------------------------------");
        \Log::info("cron primera_segunda_asistencia");

        // EVENTOS QUE ESTAN EN TRANSCURSO
        // $eventos = Eventos::where('estado_id', 3)->get(['id', 'usuario_id', 'duracion', 'hora_inicio_real', 'fecha_inicio']);
        $eventos = Eventos::with([
                            'actividad_invitados' => function($query){
                                $query->select('actividades_asistencia.usuario_id', 'actividades_asistencia.evento_id', 'actividades_asistencia.id', 'actividades_asistencia.estado')
                                    ->where('actividades_asistencia.estado', 1);
                            },
                        ])
                        ->where('estado_id', 3)
                        // ->where('id', 133)
                        ->get(['id', 'usuario_id', 'duracion', 'hora_inicio_real', 'fecha_inicio']);
        $this->info("EVENTOS EN TRANSCURSO : $eventos");
        \Log::info("EVENTOS EN TRANSCURSO : $eventos");

        // foreach ($eventos_test as $ev) {
        //     foreach ($ev->actividad_invitados as $as) {
        //         \Log::info($as->usuario_id);
        //     }
        // }

        $ahora = Carbon::now()->format('Y-m-d H:i');
        $ahora2 = Carbon::now()->format('Y-m-d H:i:s');
        // $this->info("AHORA 1 ==> $ahora");
        // $this->info("AHORA 2 ==> $ahora2");

        // $this->info("EVENTOS EN TRANSCURSO ===> $eventos");


        foreach ($eventos as $ev) {
            // $creador_evento = AsistenteEvento::where('usuario_id', $ev->usuario_id)->where('evento_id', $ev->id)->first(['fecha_ingreso']);
            // $fecha_inicio_evento1 = new Carbon(date('Y-m-d h:i:s', strtotime($creador_evento->fecha_ingreso)));
            // $fecha_inicio_evento2 = new Carbon(date('Y-m-d h:i:s', strtotime($creador_evento->fecha_ingreso)));
            $fecha_inicio_evento1 = new Carbon(date('Y-m-d H:i:s', strtotime($ev->fecha_inicio)));
            $fecha_inicio_evento2 = new Carbon(date('Y-m-d H:i:s', strtotime($ev->fecha_inicio)));
            // $this->info($fecha_inicio_evento1);
            $duracion1 = intval($ev->duracion * 0.2);
            $duracion2 = intval($ev->duracion * 0.8);
            $fecha_inicio_evento1->addMinutes($duracion1);
            $fecha_inicio_evento2->addMinutes($duracion2);
            // $this->info("primera asistencia : $fecha_inicio_evento1");
            // $this->info("segunda asistencia : $fecha_inicio_evento2");
            // $asistentes_activos = ActividadEvento::where('evento_id', $ev->id)->where('estado', 1)->get(['usuario_id']);
            // $this->info("Asistentes activos ====> $asistentes_activos");

            $_as = collect();
            foreach ($ev->actividad_invitados as $as) {
                $_as->push($as->usuario_id);
            }
            \Log::info("USUARIOS ACTIVOS => ". count($_as));

            $this->info("Asistentes activos ===> $_as , EVENTO_ID ====> $ev->id");
            \Log::info("USUARIOS ACTIVOS => ". count($_as));

            \Log::info("AHORA => ". strtotime($ahora));
            \Log::info("AHORA => ". $ahora);
            \Log::info("FECHA EVENTO 1 => ". strtotime($fecha_inicio_evento1->format('Y-m-d H:i')));
            \Log::info("FECHA EVENTO 1 => ". $fecha_inicio_evento1->format('Y-m-d H:i'));

            if (strtotime($ahora) == strtotime($fecha_inicio_evento1->format('Y-m-d H:i'))) {
                AsistenteEvento::where('evento_id', '=', $ev->id)
                    ->whereIn('usuario_id', $_as)
                    ->update(['primera_asistencia' => $ahora2]);
                \Log::info("Se marco la primera asistencia para los usuarios $_as con la hora $ahora2 ---- evento_id $ev->id");
            } else if (strtotime($ahora) == strtotime($fecha_inicio_evento2->format('Y-m-d H:i'))) {
                AsistenteEvento::where('evento_id', '=', $ev->id)
                    ->whereIn('usuario_id', $_as)
                    ->update(['segunda_asistencia' => $ahora2]);
                \Log::info("Se marco la segunda asistencia para los usuarios $_as con la hora $ahora2 ---- evento_id $ev->id");
            }
        }
        \Log::info("----------------------------------------------------------------------------------------------------------------------------------------");

        $this->info('Se marcaron todas las asistencias que cumplen con las condiciones.');
    }
}
