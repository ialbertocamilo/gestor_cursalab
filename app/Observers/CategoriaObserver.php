<?php

namespace App\Observers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Categoria;
use App\Models\Resumen_x_curso;

class CategoriaObserver
{
    /**
     * Handle the categoria "created" event.
     *
     * @param  \App\Categoria  $categoria
     * @return void
     */
    public function created(Categoria $categoria)
    {
        //
    }

    /**
     * Handle the categoria "updated" event.
     *
     * @param  \App\Categoria  $categoria
     * @return void
     */
    public function updated(Categoria $categoria)
    {
        if($categoria->isDirty('estado')){
            $cursos = $categoria->cursos;
            foreach ($cursos as $cur) {
                Curso::find($cur->id)->update(['estado'=>$categoria->estado]);
            }
            $cursos_id = $cursos->pluck('id');
            if(count($cursos_id)>0){
                Posteo::whereIn('curso_id',$cursos_id)->update(['estado'=>$categoria->estado]);
            }
        }
        if($categoria->isDirty('modalidad')){
            $libre = ($categoria->modalidad == 'libre') ? 1 : 0 ;
            $cursos = $categoria->cursos;
            foreach ($cursos as $cur) {
                Curso::find($cur->id)->update(['libre'=>$libre]);
            }
            $cursos_id = $cursos->pluck('id');
            Resumen_x_curso::whereIn('curso_id',$cursos_id)->update(['libre'=>$libre]);
        }
    }

    /**
     * Handle the categoria "deleted" event.
     *
     * @param  \App\Categoria  $categoria
     * @return void
     */
    public function deleted(Categoria $categoria)
    {
        //
    }

    /**
     * Handle the categoria "restored" event.
     *
     * @param  \App\Categoria  $categoria
     * @return void
     */
    public function restored(Categoria $categoria)
    {
        //
    }

    /**
     * Handle the categoria "force deleted" event.
     *
     * @param  \App\Categoria  $categoria
     * @return void
     */
    public function forceDeleted(Categoria $categoria)
    {
        //
    }
}
