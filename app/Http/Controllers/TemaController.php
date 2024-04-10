<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Role;
use App\Models\Curso;
use App\Models\Media;
use App\Models\Topic;
use App\Models\Course;
use App\Models\Posteo;

use App\Models\School;

use App\Models\Ability;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Ambiente;
use App\Models\Pregunta;
use App\Models\Question;
use App\Models\Taxonomy;
use App\Models\Categoria;
use App\Models\MediaTema;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Models\AssignedRole;
use App\Models\SortingModel;
use Illuminate\Http\Request;
use App\Models\TagRelationship;
use Illuminate\Support\Facades\DB;
use App\Models\TopicAssistanceUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
        $request->course_code = $course->modality->code;
        $temas = Topic::search($request);

        PosteoSearchResource::collection($temas);
        return $this->success($temas);
    }
    public function getSelects(School $school,Course $course){
        $course_offline = MediaTema::dataSizeCourse($course);
        return $this->success(['course_offline'=>$course_offline]);
    }
    public function getFormSelects(School $school, Course $course, Topic $topic = null, $compactResponse = false)
    {
        // $tags = []; //Tag::select('id', 'nombre')->get();
        $tags = Taxonomy::where('group','tags')->whereIn('type',['competency','hability','level'])->select('id','name','type','description')->get();
        $q_requisitos = Topic::select('id', 'name')->where('course_id', $course->id);
        if ($topic) {
            $q_requisitos->whereNotIn('id', [$topic->id]);
        }

        $filters = ['course_id' => $course->id];
        $default_position = $max_position = SortingModel::getNextItemOrderNumber(Topic::class, $filters, 'position');

        $requisitos = $q_requisitos->orderBy('position')->get();

        $evaluation_types = Taxonomy::getDataForSelect('topic', 'evaluation-type');
        $qualification_types = Taxonomy::getDataForSelect('system', 'qualification-type');

        $qualification_type = $course->qualification_type;
        $course_code_modality = $course->modality->code;

        $media_url = get_media_root_url();

        $limits_ia_convert = Workspace::getLimitAIConvert($topic);
        $has_permission_to_use_ia_evaluation = Ability::hasAbility('course','jarvis-evaluations');
        $has_permission_to_use_ia_description = Ability::hasAbility('course','jarvis-descriptions');
        $hosts = Usuario::getCurrentHosts(false,null,'get_records',[DB::raw("CONCAT(document,' - ',CONCAT_WS(' ',name,lastname,surname)) as 'document_fullname'")]);
        $has_permission_to_use_tags = boolval(get_current_workspace()->functionalities()->get()->where('code','show-tags-topics')->first());
        $workspace_id = get_current_workspace()->id;
        // $dinamyc_link = Taxonomy::getFirstData(group:'system', type:'env', code:'dynamic-link-multi')?->name;
        $dinamyc_link = 'https://app.cursalab.io';
        $gestor_url = env('APP_URL');
        if(Str::contains($gestor_url, 'inretail') ){
            $dinamyc_link = 'https://inretail.cursalab.io';
        }
        if(Str::contains($gestor_url, 'potenciandotutalentongr') ){
            $dinamyc_link = 'https://potenciandotutalentongr.pe';
        }
        if(Str::contains($gestor_url, 'campusaustralgroup') ){
            $dinamyc_link = 'https://campusaustralgroup.com';
        }
        $dinamyc_link = $dinamyc_link.'/lista-reuniones';

        $is_offline = $course->is_offline;
        $response = compact('tags', 'requisitos', 'evaluation_types', 'qualification_types', 'qualification_type',
                             'media_url', 'default_position', 'max_position','limits_ia_convert',
                             'has_permission_to_use_ia_evaluation','has_permission_to_use_ia_description','has_permission_to_use_tags',
                             'hosts','course_code_modality','workspace_id','dinamyc_link','is_offline');

        return $compactResponse ? $response : $this->success($response);
    }

    public function searchTema(School $school, Course $course, Topic $topic)
    {
         // Checks whether current user is a super user
        Auth::checK();
        $isSuperUser = AssignedRole::hasRole(Auth::user()->id, Role::SUPER_USER);
        $topic->media = MediaTema::where('topic_id', $topic->id)->orderBy('position')->get();
        if($school->code == 'cursalab-university'){
            $topic->media->map(function($m){
                $m['value'] = '**********';
                return $m;
            });
        }
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
        $topic->tags = $topic->tags()->pluck('tag_id');
        $media_url = get_media_root_url();
        $limits_ia_convert = Workspace::getLimitAIConvert($topic);
        $has_permission_to_use_ia_evaluation = Ability::hasAbility('course','jarvis-evaluations');
        $has_permission_to_use_ia_description = Ability::hasAbility('course','jarvis-descriptions');
        $has_permission_to_use_tags = boolval(get_current_workspace()->functionalities()->get()->where('code','show-tags-topics')->first());
        return $this->success([
            'tema' => $topic,
            'tags' => $form_selects['tags'],
            'requisitos' => $form_selects['requisitos'],
            'workspace_id' => $form_selects['workspace_id'],
            'hosts' =>  $form_selects['hosts'],
            'course_code_modality' => $form_selects['course_code_modality'],
            'is_offline' => $form_selects['is_offline'],
            'evaluation_types' => $form_selects['evaluation_types'],
            'qualification_types' => $form_selects['qualification_types'],
            'dinamyc_link' => $form_selects['dinamyc_link'],
            'media_url' => $media_url,
            'limits_ia_convert'=>$limits_ia_convert,
            'has_permission_to_use_ia_evaluation'=>$has_permission_to_use_ia_evaluation,
            'has_permission_to_use_ia_description' => $has_permission_to_use_ia_description,
            'has_permission_to_use_tags' => $has_permission_to_use_tags
        ]);
    }

    public function store(School $school, Course $course, TemaStoreUpdateRequest $request)
    {
        $data = $request->validated();

        // Validate storage limit

        $files = [];
        if (isset($data['file_imagen']))  $files[] = $data['file_imagen'];
        foreach ($data['medias'] as $media) {
            if (isset($media['file']))  $files[] = $media['file'];
        }
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);
        if (!$hasStorageAvailable) {
            return response()->json([
                'message' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }

        // Save topic

        //Validación para temas de un curso virtual: Valida si es que existe una cuenta disponible en el horario especificado
        if(!Topic::validateAvaiableAccount($course,$data)){
            return $this->error('No se puede crear la sesión virtual debido a que no hay una cuenta disponible en el horario elegido.');
        }
        $data = $this->uploadQRTopic($data);
        $data = Media::requestUploadFile($data, 'imagen');

        $tema = Topic::storeRequest($data);
        $course->storeUpdateMeeting();
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

        // Validate storage limit

        $files = [];
        if (isset($data['file_imagen']))  $files[] = $data['file_imagen'];
        foreach ($data['medias'] as $media) {
            if (isset($media['file']))  $files[] = $media['file'];
        }
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);
        if (!$hasStorageAvailable) {
            return response()->json([
                'message' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }

        // Save topic

        if(!Topic::validateAvaiableAccount($course,$data,$topic)){
            return $this->error('No se puede crear la sesión virtual debido a que no hay una cuenta disponible en el horario elegido.');
        }
        $data = $this->uploadQRTopic($data);
        $data = Media::requestUploadFile($data, 'imagen');

        // info($data);
        if ($data['validate']):
            $validations = Topic::validateBeforeUpdate($school, $topic, $data);
//            info($validations['list']);
            if (count($validations['list']) > 0)
                return $this->success(compact('validations'), 'Ocurrió un error.', 422);
        endif;

        $topic = Topic::storeRequest($data, $topic);
        $course->storeUpdateMeeting();
        $response = [
            'tema' => $topic,
            'msg' => ' Tema actualizado correctamente.',
            'messages' => Topic::getMessagesAfterUpdate($topic, $data, 'Tema actualizado con éxito')
        ];

        return $this->success($response);
    }
    private function uploadQRTopic($data){
        if(isset($data['path_qr']) && str_contains($data['path_qr'], 'base64')){
            $name =  'qr/'.Str::slug($data['name']).'-'.get_current_workspace()?->id . '-' . date('YmdHis') . '-' . Str::random(3);
            $name = Str::of($name)->limit(100);
            $path = $name.'.png';
            $media = Media::uploadMediaBase64('', $path, $data['path_qr'],false);
            $data['path_qr'] = get_media_url($path,'s3');
        }
        return $data;
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
    public function getHosts(){
        $hosts = Usuario::getCurrentHosts(false,null,'get_records',[DB::raw("CONCAT(document,CONCAT_WS(' ',name,lastname,surname)) as 'document_fullname'")]);
        return $this->success(['hosts'=>$hosts]);
    }
    ////////////////////// Tema ENCUESTA ////////////////////////////

    public function getEncuesta(School $school, Course $course,Topic $topic)
    {
        $workspace = get_current_workspace();

        $encuestas = Poll::select('titulo as nombre', 'id')->whereRelation('type','code','xtema')->where('workspace_id', $workspace->id)->get();
        $encuestas->prepend(['nombre' => 'Ninguno', 'id' => "ninguno"]);
        $topic->encuesta_id = $topic->poll_id ?? "ninguno";
        return $this->success(['encuestas'=>$encuestas,'course'=>$topic]);
    }

    public function storeUpdateEncuesta(School $school, Course $course, Topic $topic,Request $request)
    {
        $data = $request->all();
        $poll_id = $data['encuesta_id'] === "ninguno" ? null : $data['encuesta_id'];
        $topic->poll_id = $poll_id;
        $topic->save();
        cache_clear_model(Topic::class);

        return $this->success(['msg' => 'Encuesta de curso actualizada.']);
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
                'rptas_json' => json_encode($options),
                'rpta_ok' => $question['correctAnswer'] + 1,
                'active' => 1,
                'required' => 0,
                'score' => calculateValueForQualification($score, 20, $topic->qualification_type->position),
            ];
        }
        Question::insert($insert_questions);
        // dd($insert_questions);
        $data = Question::verifyEvaluation($topic);
        $data = array_merge($data,['message'=>'Preguntas creadas correctamente.']);
        return $this->success($data);
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

    public function listMedias($school, $course, Topic $topic){
        $topic->load('medias:id,value,type_id,topic_id,title as name');
        $topic->medias->transform(function ($media) use($topic) {
            $media->url = $topic->generateMediaUrl($media->type_id, $media->value);
            return $media;
        });
        return $this->success(['topics'=>[$topic]]);
    }

    public function downloadReportAssistance($school_id,$course_id,$topic_id){
        return TopicAssistanceUser::generatePDFDownload($course_id,$topic_id);
    }

    protected function downloadQuestions($school, $course, Topic $topic){
        $data = Question::getListQuestionToReport($topic);
        return $this->success($data);
    }
}
