<?php

namespace App\Observers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Resumen_x_curso;
use Illuminate\Support\Facades\DB;

class CursoObserver
{
    /**
     * Handle the curso "created" event.
     *
     * @param  $curso
     * @return void
     */
    public function created(Curso $curso)
    {
        if($curso->estado){
            DB::table('categorias')->where('id',$curso->categoria_id)->update([
                'estado'=>1
            ]);
        }
    }

    /**
     * Handle the curso "updated" event.
     *
     * @param  \App\Models\Curso  $curso
     * @return void
     */
    public function updated(Curso $curso)
    {
        if($curso->isDirty('estado')){
            if(!($curso->estado)){
                Resumen_x_curso::where('curso_id',$curso->id)->update(['estado_rxc'=>$curso->estado]);
            }else{
                //SI SE ACTIVA UN CURSO LA CATEGORIA DEBE ACTIVARSE
                DB::table('categorias')->where('id',$curso->categoria_id)->update([
                    'estado'=>1
                ]);
            }
            //SI SE DESACTIVA EL ULTIMO CURSO SE DEBE DESACTIVAR LA CATEGORIA
            $cursos_activos = Curso::where('categoria_id',$curso->categoria_id)->where('estado',1)->get();
            if(count($cursos_activos)==0){
                DB::table('categorias')->where('id',$curso->categoria_id)->update([
                    'estado'=>0
                ]);
            }
            $accion = ($curso->estado==1) ? 'curso_activado' : 'curso_desactivado' ;
            $this->create_update_usuario($curso,$accion,null);

            Posteo::where('curso_id', $curso->id)->update([
                'estado' => $curso->estado
            ]);
        }
        if($curso->isDirty('libre')){
            $this->create_update_usuario($curso,'cambio_curso_libre',null);
        }
    }

    /**
     * Handle the curso "deleted" event.
     *
     * @param  \App\Models\Curso  $curso
     * @return void
     */
    public function deleted(Curso $curso)
    {
        Resumen_x_curso::where('curso_id',$curso->id)->update(['estado_rxc'=>0]);
        $this->create_update_usuario($curso,'curso_eliminado',$curso->categoria_id);
    }

    /**
     * Handle the curso "restored" event.
     *
     * @param  \App\Models\Curso  $curso
     * @return void
     */
    public function restored(Curso $curso)
    {
        //
    }

    /**
     * Handle the curso "force deleted" event.
     *
     * @param  \App\Models\Curso  $curso
     * @return void
     */
    public function forceDeleted(Curso $curso)
    {
        //
    }
    private function create_update_usuario($curso,$accion,$categoria_id){
        $s=DB::table('update_usuarios')->where('tipo','update_resumenes_curso')->where('curso_id',$curso->id)->where('estado',0)->first();
        if(is_null($s)){
            DB::table('update_usuarios')->insert([
                'categoria_id' => $categoria_id,
                'curso_id' =>$curso->id,
                'tipo' => 'update_resumenes_curso',
                'accion' => $accion,
                'extra_info'=> $curso->nombre
            ]);
        }
    }
}
