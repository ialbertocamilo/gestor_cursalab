<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use App\Models\Curso;
use App\Models\Media;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\School;
use App\Models\Abconfig;

use App\Models\Pregunta;

use App\Models\Question;
use App\Models\Taxonomy;
use App\Models\Categoria;
use App\Models\MediaTema;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\TagRelationship;
use App\Http\Requests\Tema\TemaStoreUpdateRequest;
use App\Http\Resources\Posteo\PosteoSearchResource;
use App\Http\Resources\Posteo\PosteoPreguntasResource;
use App\Http\Requests\TemaPregunta\TemaPreguntaStoreRequest;
use App\Http\Requests\TemaPregunta\TemaPreguntaDeleteRequest;
use App\Http\Requests\TemaPregunta\TemaPreguntaImportRequest;

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

    public function getFormSelects(School $school, Course $course, Topic $topic = null, $compactResponse = false)
    {
        $tags = []; //Tag::select('id', 'nombre')->get();
        $q_requisitos = Topic::select('id', 'name')->where('course_id', $course->id);
        if ($topic)
            $q_requisitos->whereNotIn('id', [$topic->id]);

        $requisitos = $q_requisitos->orderBy('position')->get();

        $evaluation_types = Taxonomy::getDataForSelect('topic', 'evaluation-type');
        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');

        $qualification_type = $course->qualification_type;
        $media_url = get_media_root_url();

        $response = compact('tags', 'requisitos', 'evaluation_types', 'qualification_types', 'qualification_type', 'media_url');

        return $compactResponse ? $response : $this->success($response);
    }

    public function searchTema(School $school, Course $course, Topic $topic)
    {
        $topic->media = MediaTema::where('topic_id', $topic->id)->orderBy('position')->get();
        $topic->tags = [];

        $topic->load('qualification_type');

        $topic->hide_evaluable = $topic->assessable;
        $topic->hide_tipo_ev = $topic->type_evaluation_id;

        $topic->disabled_estado_toggle = false;
        $topic->has_qualified_questions = $topic->countQuestionsByTypeEvaluation('select-options') > 0;
        $topic->has_open_questions = $topic->countQuestionsByTypeEvaluation('written-answer') > 0;

        $topic->disabled_estado_toggle = $topic->assessable === 1 &&
            $topic->countQuestionsByTypeEvaluation($topic->evaluation_type->code === 'qualified' ? 'select-options' : 'written-answer') === 0;

        $form_selects = $this->getFormSelects($school, $course, $topic, true);
        $topic->tipo_ev = $topic->hide_tipo_ev;
        $requirement = $topic->requirements()->first();
        $requirement && $topic->topic_requirement_id =  $requirement->requirement_id;

        $media_url = get_media_root_url();

        return $this->success([
            'tema' => $topic,
            'tags' => $form_selects['tags'],
            'requisitos' => $form_selects['requisitos'],
            'evaluation_types' => $form_selects['evaluation_types'],
            'qualification_types' => $form_selects['qualification_types'],
            'media_url' => $media_url,
            'limits_ia_convert'=>$limits_ia_convert
        ]);
    }

    public function store(School $school, Course $course, TemaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');

        $tema = Topic::storeRequest($data);

        $response = [
            'tema' => $tema,
            'msg' => ' Tema creado correctamente.',
            'messages' => [
                'list' => [],
                'title' => null,
                'type' => 'validations-after-update'
            ],
        ];

        return $this->success($response);
    }

    public function update(School $school, Course $course, Topic $topic, TemaStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'imagen');
        // info($data);

        if ($data['validate']):
            $validations = Topic::validateBeforeUpdate($school, $topic, $data);
//            info($validations['list']);
            if (count($validations['list']) > 0)
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
        $data = $request->all();
        $data['to_delete'] = true;
        $data['active'] = $topic->active;

        if ($data['validateForm']):
            $validations = Topic::validateBeforeDelete($school, $topic, $data);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error.', 422);
        endif;

        $topic->delete();

        $tema_evaluable = Topic::where('course_id', $course->id)->where('assessable', ACTIVE)->first();
        $course->assessable = $tema_evaluable ? 1 : 0;
        $course->save();

        $response = [
            'tema' => $topic,
            'msg' => ' Tema eliminado correctamente.',
            'messages' => Topic::getMessagesAfterDelete($topic, 'Tema eliminado con éxito')
        ];

        return $this->success($response);
    }

    public function updateStatus(School $school, Course $course, Topic $topic, Request $request)
    {
        $active = !$topic->active;
//        info($request->all());
        if ($request->validateForm):
            $validations = Topic::validateBeforeUpdate($school, $topic, ['active' => $active]);

            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error.', 422);
        endif;

        $topic->active = $active;
        $topic->save();

        $response = [
            'tema' => $topic,
            'msg' => ' Estado actualizado con éxito.',
            'messages' => Topic::getMessagesAfterUpdate($topic, ['active' => $active], 'Tema actualizado con éxito')
        ];

        return $this->success($response);
    }

    // ========================================== EVALUACIONES TEMAS ===================================================
    public function preguntas_list(School $school, Course $course, Topic $topic, Request $request)
    {
        $data = Question::verifyEvaluation($topic);

        // dd($data);

        return view('temas.preguntas_list', $data);
    }

    public function search_preguntas(School $school, Course $course, Topic $topic, Request $request)
    {
        $request->merge(['tema_id' => $topic->id, 'current_qualification_value' => $topic->qualification_type->position]);

        $preguntas = Topic::search_preguntas($request, $topic);

        PosteoPreguntasResource::collection($preguntas);

        return $this->success($preguntas);
    }

    public function showPregunta(School $school, Course $course, Topic $topic, Question $pregunta)
    {
        // $pregunta->rptas_json = json_decode($pregunta->rptas_json);

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

        $pregunta->score = calculateValueForQualification($pregunta->score, $topic->qualification_type->position);

        return $this->success(['pregunta' => $pregunta]);
    }
    public function storeAIQuestion(School $school, Course $course, Topic $topic,Request $request){
        $question_type_code = $topic->evaluation_type->code === 'qualified'
        ? 'select-options'
        : 'written-answer';
        $type_id =  Taxonomy::getFirstData('question', 'type', $question_type_code)?->id;
        $questions = $request->all();
        $insert_questions = [];
        $score = (int)(20/count($questions));
        foreach ($questions as $question) {
            $options = [];
            foreach ($question['options'] as $key => $option) {
                $options[$key+1] = $option['text'];
            }
            $insert_questions[] = [
                'pregunta' => $question['question'],
                'topic_id' => $topic->id,
                'type_id' => $type_id,
                // 'rptas_json' => $data['nuevasRptas'],
                'rptas_json' => json_encode($options),
                'rpta_ok' => $question['correctAnswer'] + 1,
                'active' => 1,
                'required' => 0,
                'score' => calculateValueForQualification($score, 20, $topic->qualification_type->position),
            ];
        }
        Question::insert($insert_questions);
        // dd($insert_questions);
        return $this->success(['message'=>'Preguntas creadas correctamente.']);
        // $result = Question::checkScoreLeft($topic, $data['id'], $data);
    }
    public function storePregunta(
        School $school, Course $course, Topic $topic, TemaPreguntaStoreRequest $request
    )
    {
        $data = $request->validated();

        $question_type_code = $topic->evaluation_type->code === 'qualified'
            ? 'select-options'
            : 'written-answer';

        $result = Question::checkScoreLeft($topic, $data['id'], $data);

        if ($result['status'])
            return $this->error($result['message'], 422, ['errors' => [$result['message']]]);

        Question::updateOrCreate(
            ['id' => $data['id']],
            [
                'topic_id' => $topic->id,
                'type_id' => Taxonomy::getFirstData('question', 'type', $question_type_code)?->id,
                // 'pregunta' => html_entity_decode($data['pregunta']),
                // 'rptas_json' => html_entity_decode($data['nuevasRptas']),
                'pregunta' => $data['pregunta'],
                // 'rptas_json' => $data['nuevasRptas'],
                'rptas_json' => json_decode($data['nuevasRptas'], true),
                'rpta_ok' => $data['rpta_ok'],
                'active' => $data['active'],
                'required' => $data['required'] ?? NULL,
                'score' => $data['score'] ? calculateValueForQualification($data['score'], 20, $topic->qualification_type->position) : NULL,
                // 'position' => 'despues'
            ]
        );

        // Si se desactiva la última pregunta del tema, según su tipo de evaluación ($tipo_pregunta)
        // se inactivará el tema
        // TODO: agregar modal de validación en listado de preguntas
        $has_active_questions = $topic->questions()->whereRelation('type', 'code', $question_type_code)
                ->where('active', '1')->count() > 0;
//        info($has_active_questions);
//        info($question_type_code);
        if (!$has_active_questions):
            $topic->active = 0;
            $topic->save();
        endif;

        $data = Question::verifyEvaluation($topic);
        $data['msg'] = 'Pregunta actualizada. ' . ($data['message'] ?? '');

        return $this->success($data);
    }

    public function importPreguntas(
        School $school, Course $course, Topic $topic, TemaPreguntaImportRequest $request
    )
    {
        $data = $request->validated();

        // Load topic evaluation type from database type_evaluation_id

        $evaluationType = Taxonomy::where('group', 'topic')
                                  ->where('type', 'evaluation-type')
                                  ->where('code', 'qualified')
                                  ->first();

        $data['topic_id'] = $topic->id;
        $data['isQualified'] = $evaluationType->id === $topic->type_evaluation_id;

        $result = Question::import($data);

        if ($result['status'] == 'error') {

            return $this->success($result);
        }

        $data = Question::verifyEvaluation($topic);

        // if ($result['status'] == 'error') {

        //     return $this->error($result);
        // }

        return $this->success($data);
    }

    public function deletePregunta(School $school, Course $course, Topic $topic, Question $pregunta)
    {
        $question_type_code = $topic->evaluation_type->code === 'qualified'
            ? 'select-options'
            : 'written-answer';
        $pregunta->delete();

        //        $tema_evaluable = Posteo::where('curso_id', $curso->id)->where('evaluable', 'si')->first();
        //        $curso->c_evaluable = $tema_evaluable ? 'si' : 'no';
        //        $curso->save();

        // Si se elimina la última pregunta del tema, según su tipo de evaluación ($tipo_pregunta)
        // se inactivará el tema
        // TODO: agregar modal de validación en listado de preguntas
        $has_active_questions = $topic->questions()->whereRelation('type', 'code', $question_type_code)
                ->where('active', '1')->count() > 0;

        if (!$has_active_questions) :
            $topic->active = 0;
            $topic->save();
        endif;

        $data = Question::verifyEvaluation($topic);
        $data['msg'] = 'Eliminado correctamente. ' . ($data['message'] ?? '');

        return $this->success($data);
    }
}
