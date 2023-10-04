<?php

namespace App\Http\Resources\Posteo;

use Illuminate\Http\Resources\Json\JsonResource;

class PosteoSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $topic = $this;

        $questions_count = $topic->assessable ?
            ($topic->evaluation_type->code === 'qualified' ?
                $topic->questions->where('type.code', 'select-options')->count()
                : $topic->questions->where('type.code', 'written-answer')->count()
            )
            : null;
        //        info($topic->questions);
        //        info( $topic->questions->where('type.code', 'select-options')->count());
        return [
            'id' => $topic->id,
            'nombre' => $topic->name,
            'nombre_and_requisito' => [
                'nombre' => $topic->name,
                'requisito' => $topic->requirements->first()->model_topic->name ?? null,
            ],
            'tipo_evaluacion' => $topic->evaluation_type->name ?? '---', //$topic->getTipoEvaluacion(),
            'image' => get_media_url($topic->imagen),
            'active' => (bool)$topic->active,
            'position' => $topic->position,
            'assessable' => $topic->assessable ? 'Sí' : 'No',
            'es_evaluable' => $topic->assessable,
            //            'preguntas_count' => $topic->questions_count,
            'preguntas_count' => $questions_count,

            'edit_route' => route('temas.editTema', [$request->school_id, $request->course_id, $topic->id]),
            'evaluacion_route' => route('temas.preguntas_list', [$request->school_id, $request->course_id, $topic->id]),
            'is_super_user'=>auth()->user()->isAn('super-user')
            // 'is_super_user'=> true

        ];
    }
}
