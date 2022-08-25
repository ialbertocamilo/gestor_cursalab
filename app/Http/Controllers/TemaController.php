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
    public function search(School $school, Course $course, Request $request)
    {
        $request->school_id = $school->id;
        $request->course_id = $course->id;
        $temas = Topic::search($request);

        PosteoSearchResource::collection($temas);

        return $this->success($temas);
    }

    public function searchTema(School $school, Course $course, Topic $topic)
    {
        $topic->media = MediaTema::where('topic_id', $topic->id)->orderBy('position')->get();
        $topic->tags = [];

        $topic->hide_evaluable = ($topic->assessable == 1) ? 1 : 0;

        $topic->hide_tipo_ev = '';
        if (!is_null($topic->type_evaluation_id)) {
            $tax_type_eval = Taxonomy::where('group', 'topic')->where('type', 'evaluation-type')->where('id', $topic->type_evaluation_id)->first();
            $topic->hide_tipo_ev = $tax_type_eval->code;
        }

        $tax_select = Taxonomy::where('group', 'question')->where('type', 'type')->where('code', 'select-optionsa')->first();

        $topic->disabled_estado_toggle = false;
        $topic->cant_preguntas_evaluables_activas = 0;
        if (!is_null($tax_select)) {
            $preguntas_evaluables = $topic->questions->where('active', 1)->where('type_id', $tax_select->id);
            $topic->disabled_estado_toggle = $topic->assessable == 1 && $preguntas_evaluables->count() === 0;
            $topic->cant_preguntas_evaluables_activas = $preguntas_evaluables->count();
        }
        $form_selects = $this->getFormSelects($school, $course, $topic, true);
        $topic->tipo_ev = $topic->hide_tipo_ev;

        return $this->success([
            'tema' => $topic,
            'tags' => $form_selects['tags'],
            'requisitos' => $form_selects['requisitos'],
            'evaluation_types' => $form_selects['evaluation_types']
        ]);
    }

    public function getFormSelects(School $school, Course $course, Topic $topic = null, $compactResponse = false)
    {
        $tags = []; //Tag::select('id', 'nombre')->get();
        $q_requisitos = Topic::select('id', 'name')->where('course_id', $course->id);
        if ($topic)
            $q_requisitos->whereNotIn('id', [$topic->id]);

        $requisitos = $q_requisitos->orderBy('position')->get();

        $evaluation_types = Taxonomy::getDataForSelect('topic', 'evaluation-type');

        $response = compact('tags', 'requisitos', 'evaluation_types');

        return $compactResponse ? $response : $this->success($response);
    }

    public function store(School $school, Course $course, TemaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');

        $tema = Topic::storeRequest($data);

        $response = [
            'tema' => $tema,
            'msg' => ' Tema creado correctamente.',
            'messages' => [],
        ];

//        $response['messages'] = Topic::getMessagesActions($tema, $data, 'Tema creado con éxito');

        return $this->success($response);
    }

    public function update(School $school, Course $course, Topic $topic, TemaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');

        $validations = Topic::validateBeforeUpdate($school, $course, $topic, $data);

        if ($data['validate']):
            if ($validations['list'] > 0)
                return $this->success(compact('validations'), 'Ocurrió un error.', 422);
        endif;

        $topic = Topic::storeRequest($data, $topic);

        $response = [
            'tema' => $topic,
            'msg' => ' Tema actualizado correctamente.',
            'messages' => Topic::getMessagesAfterUpdate($topic, $data, 'Tema actualizado con éxito')
        ];

        return $this->success($response);
    }

    public function destroy(School $school, Course $course, Topic $topic, Request $request)
    {
        // TODO: Para la segunda fase
//        if ($request->withValidations == 0) {
//            $validate = Topic::validateTemaEliminar($topic, $course);
//            //        dd($validate);
//
//            if (!$validate['validate'])
//                return $this->success(compact('validate'), 422);
//        }

        $topic->delete();

//        $tema_evaluable = Topic::where('course_id', $course->id)->where('assessable', ACTIVE)->first();
//        $course->assessable = $tema_evaluable ? 1 : 0;
//        $course->save();

        $response = [
            'tema' => $topic,
            'msg' => ' Tema eliminado correctamente.'
        ];

//        $response['messages'] = Topic::getMessagesActions($topic, [], 'Tema eliminado con éxito');

        return $this->success($response);
    }

    public function updateStatus(School $school, Course $course, Topic $topic, Request $request)
    {
//        $active = !(($topic->active === 1));
        $active = !$topic->active;

//        if ($request->withValidations == 0) {
//            $validate = Topic::validateTemaUpdateStatus($school, $course, $topic, $active);
//            //        dd($validate);
//
//            if (!$validate['validate'])
//                return $this->success(compact('validate'), 422);
//        }

        $topic->active = $active;
        $topic->save();

        $response = [
            'tema' => $topic,
            'msg' => ' Estado actualizado con éxito.'
        ];

//        $response['messages'] = Topic::getMessagesActions($topic, [], 'Tema actualizado con éxito');

        return $this->success($response);
    }

    // ========================================== EVALUACIONES TEMAS ===================================================
    public function search_preguntas(School $school, Course $course, Topic $topic, Request $request)
    {
        $request->merge(['tema_id' => $topic->id]);

        $preguntas = Topic::search_preguntas($request, $topic);

        PosteoPreguntasResource::collection($preguntas);

        return $this->success($preguntas);
    }

    public function showPregunta(School $school, Course $course, Topic $topic, Question $pregunta)
    {
        $pregunta->rptas_json = json_decode($pregunta->rptas_json);

        $temp = collect();
        if ($pregunta->rptas_json) {
            foreach ($pregunta->rptas_json as $key => $rpta) {
                $temp->push([
                    'id' => (int)$key,
                    'opc' => $rpta,
                    'correcta' => $pregunta->rpta_ok == $key
                ]);
            }
        }
        $pregunta->respuestas = $temp;


        return $this->success(['pregunta' => $pregunta]);
    }

    public function storePregunta(School $school, Course $course, Topic $topic, TemaPreguntaStoreRequest $request)
    {
        $data = $request->validated();

        $tipo_preg = ($topic->tipo_ev === 'calificada') ? 'selecciona' : 'texto';

        Question::updateOrCreate(
            ['id' => $data['id']],
            [
                'topic_id' => $topic->id,
                'type_id' => $topic->type_evaluation_id,
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
        $tipo_pregunta = $topic->tipo_ev == 'calificada' ? 'selecciona' : 'texto';
        $tema_preguntas = $topic->questions->where('type_id', $topic->type_evaluation_id)->where('active', '1');

        if ($tema_preguntas->count() === 0) :
            $topic->active = 0;
            $topic->save();
        endif;

        return $this->success(['msg' => 'Pregunta actualizada']);
    }

    public function importPreguntas(School $school, Curso $course, Posteo $topic, TemaPreguntaImportRequest $request)
    {
        $data = $request->validated();

        $data['posteo_id'] = $topic->id;
        $data['tipo_ev'] = $topic->tipo_ev;

        $result = Pregunta::import($data);
        $topic->evaluable = 'si';
        $topic->tipo_ev = 'calificada';
        $topic->save();

        return $this->success($result);
    }

    public function deletePregunta(School $school, Curso $course, Posteo $topic, Pregunta $pregunta)
    {
        $pregunta->delete();

        //        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        //        $curso->c_evaluable = $tema_evaluable ? 'si' : 'no';
        //        $curso->save();

        // Si se elimina la última pregunta del tema, según su tipo de evaluación ($tipo_pregunta)
        // se inactivará el tema
        // TODO: agregar modal de validación en listado de preguntas
        $tipo_pregunta = $topic->tipo_ev == 'calificada' ? 'selecciona' : 'texto';
        $tema_preguntas = $topic->questions->where('tipo_pregunta', $tipo_pregunta)->where('estado', '1');

        if ($tema_preguntas->count() === 0) :
            $topic->estado = 0;
            $topic->save();
        endif;

        return $this->success(['msg' => 'Eliminado correctamente.']);
    }
}
