<?php

namespace App\Observers;

use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Ev_abierta;
use App\Models\Update_usuarios;
use Illuminate\Support\Facades\DB;

class PosteoObserver
{
    /**
     * Handle the posteo "created" event.
     *
     * @param \App\Posteo $posteo
     * @return void
     */
    public function created(Posteo $posteo)
    {
        // $this->create_notification($posteo);
        $this->create_update_usuario($posteo, "posteo_creado");
    }

    /**
     * Handle the posteo "updated" event.
     *
     * @param \App\Posteo $posteo
     * @return void
     */
    public function updated(Posteo $posteo)
    {
        if ($posteo->isDirty('estado') || $posteo->isDirty('evaluable') || $posteo->isDirty('tipo_ev')) {
            $this->create_update_usuario($posteo, "posteo_actualizado");
        }
        if ($posteo->isDirty('tipo_ev')) {
            $estado_en_prueba = ($posteo->tipo_ev == 'calificada') ? 1 : 0;
            $estado_en_eva = ($posteo->tipo_ev == 'abierta') ? 1 : 0;
            Prueba::where('posteo_id', $posteo->id)->update([
                'historico' => $estado_en_prueba,
            ]);
            Ev_abierta::where('posteo_id', $posteo->id)->update([
                'eva_abierta' => $estado_en_eva,
            ]);
        }

        if ($posteo->wasChanged('evaluable')) {
            $convalidar_evaluacion = request()->check_tipo_ev == 'true';
            $uu = Update_usuarios::where('tipo', 'update_resumenes_curso')
                ->where('curso_id', $posteo->curso_id)
                ->where('estado', 0)->first();
            if (json_decode($uu->extra_info)) {
                $extra_info = json_decode($uu->extra_info);
                if (!in_array($posteo->id, array_column($extra_info, 'posteo_id'))) {
                    $extra_info[] = [
                        'posteo_id' => $posteo->id,
                        'convalidar_evaluacion' => $convalidar_evaluacion,
                        'nombre' => $posteo->curso->nombre,
                        'accion' => 'posteo_actualizado',
                    ];
                    Update_usuarios::where('id', $uu->id)->update([
                        'extra_info' => json_encode($extra_info)
                    ]);
                }
            } else {
                Update_usuarios::where('id', $uu->id)->update([
                    'extra_info' => json_encode([[
                        'posteo_id' => $posteo->id,
                        'convalidar_evaluacion' => $convalidar_evaluacion,
                        'nombre' => $posteo->curso->nombre,
                        'accion' => 'posteo_actualizado',
                    ]])
                ]);
            }
        }


//        $count_temas_activos = Posteo::where('curso_id', $posteo->curso_id)->where('estado', 1)->count();
//        if ($count_temas_activos === 0){
//            $posteo->curso->update(['estado' => 0]);
//        }
    }

    /**
     * Handle the posteo "deleted" event.
     *
     * @param \App\Posteo $posteo
     * @return void
     */
    public function deleted(Posteo $posteo)
    {
        $this->create_update_usuario($posteo, "posteo_eliminado");
    }

    /**
     * Handle the posteo "restored" event.
     *
     * @param \App\Posteo $posteo
     * @return void
     */
    public function restored(Posteo $posteo)
    {
        //
    }

    /**
     * Handle the posteo "force deleted" event.
     *
     * @param \App\Posteo $posteo
     * @return void
     */
    public function forceDeleted(Posteo $posteo)
    {
        //
    }

    private function create_update_usuario($posteo, $accion)
    {
        $s = DB::table('update_usuarios')->where('tipo', 'update_resumenes_curso')->where('curso_id', $posteo->curso_id)->where('estado', 0)->first();
        if (is_null($s)) {
            DB::table('update_usuarios')->insert([
                'curso_id' => $posteo->curso_id,
                'tipo' => 'update_resumenes_curso',
                'accion' => $accion,
                'extra_info' => $posteo->curso->nombre,
            ]);
        }
    }

    // private function create_notification($data,$users){
    //     // Notification::send($users, new DefaultNotification($data));
    // }
}
