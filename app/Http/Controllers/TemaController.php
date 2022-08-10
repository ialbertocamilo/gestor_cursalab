<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Curso;
use App\Models\Media;
use App\Models\Posteo;
use App\Models\Abconfig;
use App\Models\Pregunta;
use App\Models\Categoria;
use App\Models\MediaTema;
use App\Models\TagRelationship;

use Illuminate\Http\Request;

use App\Http\Requests\Tema\TemaStoreUpdateRequest;
use App\Http\Resources\Posteo\PosteoSearchResource;
use App\Http\Resources\Posteo\PosteoPreguntasResource;
use App\Http\Requests\TemaPregunta\TemaPreguntaStoreRequest;
use App\Http\Requests\TemaPregunta\TemaPreguntaDeleteRequest;
use App\Http\Requests\TemaPregunta\TemaPreguntaImportRequest;
use App\Models\Course;
use App\Models\Post;
use App\Models\Question;
use App\Models\School;
use App\Models\Taxonomy;
use App\Models\Topic;

class TemaController extends Controller
{
    public function search(School $escuela, Course $curso, Request $request)
    {
        $request->school_id = $escuela->id;
        $request->course_id = $curso->id;
        $temas = Topic::search($request);

        PosteoSearchResource::collection($temas);

        return $this->success($temas);
    }

    public function searchTema(School $escuela, Course $curso, Topic $tema)
    {
        // $q = Question::whereHas('type', function ($t) use ($tema) {
        //     $t->where('type_id', $tema->type_evaluation_id);
        // })
        //     ->where('topic_id', $tema->id);
        $tema->media = MediaTema::where('topic_id', $tema->id)->orderBy('position')->get();
        $tema->tags = [];

        $tema->hide_evaluable = ($tema->assessable == 1) ? 'si' : 'no';

        $tema->hide_tipo_ev = '';
        if (!is_null($tema->type_evaluation_id)) {
            $tax_type_eval = Taxonomy::where('group', 'topic')->where('type', 'evaluation-type')->where('id', $tema->type_evaluation_id)->first();
            $tema->hide_tipo_ev = $tax_type_eval->code;
        }

        $tax_select = Taxonomy::where('group', 'question')->where('type', 'type')->where('code', 'select-optionsa')->first();

        $tema->disabled_estado_toggle = false;
        $tema->cant_preguntas_evaluables_activas = 0;
        if (!is_null($tax_select)) {
            $preguntas_evaluables =  $tema->questions->where('active', 1)->where('type_id', $tax_select->id);
            $tema->disabled_estado_toggle = $tema->assessable == 1 && $preguntas_evaluables->count() === 0;
            $tema->cant_preguntas_evaluables_activas = $preguntas_evaluables->count();
        }
        $form_selects = $this->getFormSelects($escuela, $curso, $tema, true);

        return $this->success([
            'tema' => $tema,
            'tags' => $form_selects['tags'],
            'requisitos' => $form_selects['requisitos']
        ]);
    }

    public function getFormSelects(School $escuela, Course $curso, Topic $tema = null, $compactResponse = false)
    {
        $tags = []; //Tag::select('id', 'nombre')->get();
        $q_requisitos = Topic::select('id', 'name')->where('course_id', $curso->id);
        if ($tema)
            $q_requisitos->whereNotIn('id', [$tema->id]);

        $requisitos = $q_requisitos->orderBy('position')->get();
        $response = compact('tags', 'requisitos');
        return $compactResponse ? $response : $this->success($response);
    }

    public function store(School $escuela, Course $curso, TemaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');

        $tema = Topic::storeRequest($data);

        $response = [
            'tema' => $tema,
            'msg' => ' Tema creado correctamente.'
        ];

        $response['messages'] = Topic::getMessagesActions($tema, $data, 'Tema creado con éxito');

        return $this->success($response);
    }

    public function update(TemaStoreUpdateRequest $request, Abconfig $abconfig, Categoria $categoria, Curso $curso, Posteo $tema)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');

        $validate = Posteo::validateTemaUpdateStatus($tema, $data['estado']);

        //        dd($validate, $data['estado']);
        if (!$validate['validate'])
            return $this->success(compact('validate'), 422);

        $tema = Posteo::storeRequest($data, $tema);

        $response = [
            'tema' => $tema,
            'msg' => ' Tema actualizado correctamente.'
        ];

        $response['messages'] = Posteo::getMessagesActions($tema, $data, 'Tema actualizado con éxito');

        return $this->success($response);
    }

    public function destroy(Abconfig $abconfig, Categoria $categoria, Curso $curso, Posteo $tema, Request $request)
    {
        if ($request->withValidations == 0) {
            $validate = Posteo::validateTemaEliminar($tema, $curso);
            //        dd($validate);

            if (!$validate['validate'])
                return $this->success(compact('validate'), 422);
        }

        $tema->delete();

        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        $curso->c_evaluable = $tema_evaluable ? 'si' : 'no';
        $curso->save();

        $response = [
            'tema' => $tema,
            'msg' => ' Tema eliminado correctamente.'
        ];

        $response['messages'] = Posteo::getMessagesActions($tema, [], 'Tema eliminado con éxito');

        return $this->success($response);
    }

    public function updateStatus(Abconfig $abconfig, Categoria $categoria, Curso $curso, Posteo $tema, Request $request)
    {
        $estado = !(($tema->estado === 1));

        if ($request->withValidations == 0) {
            $validate = Posteo::validateTemaUpdateStatus($tema, $estado);
            //        dd($validate);

            if (!$validate['validate'])
                return $this->success(compact('validate'), 422);
        }

        $tema->estado = $estado;
        $tema->save();

        $response = [
            'tema' => $tema,
            'msg' => ' Estado actualizado con éxito.'
        ];

        $response['messages'] = Posteo::getMessagesActions($tema, [], 'Tema actualizado con éxito');

        return $this->success($response);
    }

    // ========================================== EVALUACIONES TEMAS ===================================================
    public function search_preguntas(School $escuela, Course $curso, Topic $tema, Request $request)
    {
        $request->merge(['tema_id' => $tema->id]);

        $preguntas = Topic::search_preguntas($request, $tema);

        PosteoPreguntasResource::collection($preguntas);

        return $this->success($preguntas);
    }

    public function showPregunta(School $escuela, Course $curso, Topic $tema, Question $pregunta)
    {
        $pregunta->rptas_json = json_decode($pregunta->rptas_json);

        $temp = collect();
        if ($pregunta->rptas_json) {
            foreach ($pregunta->rptas_json as $key => $rpta) {
                $temp->push([
                    'id' => (int)$key,
                    'opc' => $rpta,
                    'correcta' => $pregunta->rpta_ok === (int)$key
                ]);
            }
        }
        $pregunta->respuestas = $temp;


        return $this->success(['pregunta' => $pregunta]);
    }

    public function storePregunta(School $escuela, Course $curso, Topic $tema, TemaPreguntaStoreRequest $request)
    {
        $data = $request->validated();

        $tipo_preg = ($tema->tipo_ev === 'calificada') ? 'selecciona' : 'texto';

        Question::updateOrCreate(
            ['id' => $data['id']],
            [
                'topic_id' => $tema->id,
                'type_id' => $tema->type_evaluation_id,
                'pregunta' => html_entity_decode($data['pregunta']),
                'rptas_json' => html_entity_decode($data['nuevasRptas']),
                'rpta_ok' => $data['rpta_ok'],
                'active' => $data['active'],
                // 'position' => 'despues'
            ]
        );

        // Si se desactiva la última pregunta del tema, según su tipo de evaluación ($tipo_pregunta)
        // se inactivará el tema
        // TODO: agregar modal de validación en listado de preguntas
        $tipo_pregunta = $tema->tipo_ev == 'calificada' ? 'selecciona' : 'texto';
        $tema_preguntas = $tema->questions->where('type_id', $tema->type_evaluation_id)->where('active', '1');

        if ($tema_preguntas->count() === 0) :
            $tema->active = 0;
            $tema->save();
        endif;

        return $this->success(['msg' => 'Pregunta actualizada']);
    }

    public function importPreguntas(Abconfig $abconfig, Categoria $categoria, Curso $curso, Posteo $tema, TemaPreguntaImportRequest $request)
    {
        $data = $request->validated();

        $data['posteo_id'] = $tema->id;
        $data['tipo_ev'] = $tema->tipo_ev;

        $result = Pregunta::import($data);
        $tema->evaluable = 'si';
        $tema->tipo_ev = 'calificada';
        $tema->save();

        return $this->success($result);
    }

    public function deletePregunta(Abconfig $abconfig, Categoria $categoria, Curso $curso, Posteo $tema, Pregunta $pregunta)
    {
        $pregunta->delete();

        //        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        //        $curso->c_evaluable = $tema_evaluable ? 'si' : 'no';
        //        $curso->save();

        // Si se elimina la última pregunta del tema, según su tipo de evaluación ($tipo_pregunta)
        // se inactivará el tema
        // TODO: agregar modal de validación en listado de preguntas
        $tipo_pregunta = $tema->tipo_ev == 'calificada' ? 'selecciona' : 'texto';
        $tema_preguntas = $tema->questions->where('tipo_pregunta', $tipo_pregunta)->where('estado', '1');

        if ($tema_preguntas->count() === 0) :
            $tema->estado = 0;
            $tema->save();
        endif;

        return $this->success([
            'msg' => 'Eliminado correctamente.'
        ]);
    }
}
