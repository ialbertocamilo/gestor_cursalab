<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Media;
use App\Models\Topic;
use App\Models\Course;
use App\Models\School;
use App\Models\Ambiente;

use App\Models\Taxonomy;
use App\Models\Criterion;
use App\Models\Workspace;
use App\Models\Requirement;
use Illuminate\Support\Str;
use App\Models\AssignedRole;
use App\Models\SegmentValue;
use Illuminate\Http\Request;
use App\Models\CriterionValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Models\WorkspaceFunctionality;
use Illuminate\Contracts\View\Factory;
use App\Http\Requests\WorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Http\Requests\SubWorkspaceRequest;
use App\Models\Mongo\WorkspaceCustomEmail;
use App\Http\Resources\SubWorkspaceResource;
use App\Console\Commands\reinicios_programado;
use App\Http\Requests\WorkspaceDuplicateRequest;
use Illuminate\Contracts\Foundation\Application;

class WorkspaceController extends Controller
{

    /**
     * Process request to render workspaces' selector view
     *
     * @return Application|Factory|View
     */
    public function list(): View|Factory|Application
    {
        return view(
            'workspaces.list',
            []
        );
    }
    public function list_criterios(): View|Factory|Application
    {
        return view(
            'criteria.list_in_wk',
            []
        );
    }
    public function list_criterios_values(): View|Factory|Application
    {
        return view(
            'criterion_values.list_in_wk',
            []
        );
    }

    /**
     * Process request to search workspaces
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $workspaces = Workspace::search($request);
        WorkspaceResource::collection($workspaces);

        $config = Ambiente::first();
        $config->logo = get_media_url($config->logo);

        if(ENV('MULTIMARCA') == true){
            $config->logo = 'https://statics-testing.sfo2.cdn.digitaloceanspaces.com/inretail-test2/images/wrkspc-40-wrkspc-35-logo-cursalab-2022-1-3-20230601193902-j6kjcrhock0inws-20230602170501-alIlkd31SSNTnIm.png';
            $config->titulo = 'Cursalab';

        }

        return $this->success(compact('workspaces', 'config'));
    }

    public function create(): JsonResponse
    {
        $selection = Criterion::getSelectionCheckbox();

        $workspace['criteria_workspace'] = $selection['criteria_workspace'];
        $workspace['criteria_workspace_dates'] = $selection['criteria']->where('field_type.code', 'date')->values()->all();


        $workspace['limit_allowed_users'] = null;

        $workspace['is_superuser'] = auth()->user()->isA('super-user');
        $workspace['functionalities_selected'] = [];
        $workspace['functionalities'] = Taxonomy::getDataForSelect('system', 'functionality');
        $workspace['qualification_types'] = Taxonomy::getDataForSelect('system', 'qualification-type');

        return $this->success($workspace);
    }

    public function store(WorkspaceRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Upload files

        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'logo_negativo');

        $data['limits'] = [
            'limit_allowed_media_convert' => $data['limit_allowed_media_convert'] ?? null,
            'limit_allowed_ia_evaluations' => $data['limit_allowed_ia_evaluations'] ?? null,
            'limit_descriptions_jarvis' => $data['limit_descriptions_jarvis'] ?? 0,
        ];
        $data['jarvis_configuration'] = [
            'openia_token' => $data['openia_token'] ?? '',
            'openia_model' => $data['openia_model'] ?? 'gpt-3.5-turbo',
            'context_jarvis' => $data['context_jarvis'] && $data['context_jarvis'] !='null' ? $data['context_jarvis'] : ''
        ];
        $data['reminders_configuration'] = [
            'chunk' => $data['reminders_configuration']['chunk'] ?? 0,
            'interval' => $data['reminders_configuration']['interval'] ?? 0,
        ];
        // Set constraint: limit allowed users

        if (($data['limit_allowed_users_type'] ?? false) && ($data['limit_allowed_users_limit'] ?? false)):

            $constraint_user['type'] = $data['limit_allowed_users_type'];
            $constraint_user['quantity'] = intval($data['limit_allowed_users_limit']);

            $data['limit_allowed_users'] = $constraint_user;
        else:
            $data['limit_allowed_users'] = null;
        endif;

        // Update record in database
        $data['dc3_configuration'] = json_decode($data['dc3_configuration']);
        $workspace = Workspace::create($data);

        // Save workspace's criteria

        if ( !empty($data['criteria']) ) {
            $workspace->criterionWorkspace()->sync($data['criteria']);
        }


        // Actualizar funcionalidades

        $selected_functionality = json_decode($data['selected_functionality'], true);

        foreach($selected_functionality as $fun_id => $fun) {

            $exist = WorkspaceFunctionality::where('workspace_id', $workspace->id)->where('functionality_id', $fun_id)->first();

            if($exist) {
                if(!$fun) {
                    $exist->delete();
                }
            }
            else {
                if($fun) {
                    try {

                        DB::beginTransaction();
                        $data = array('workspace_id'=> $workspace->id, 'functionality_id'=>$fun_id);
                        WorkspaceFunctionality::create($data);

                        DB::commit();
                    } catch (\Exception $e) {
                        info($e);
                        DB::rollBack();
                        abort(errorExceptionServer());
                    }
                }
            }
        }

        cache_clear_model(WorkspaceFunctionality::class);

        \Artisan::call('modelCache:clear', array('--model' => "App\Models\Criterion"));

        return $this->success(['msg' => 'Workspace creado correctamente.']);
    }

    /**
     * Process request to load record data
     *
     * @param Workspace $workspace
     * @return JsonResponse
     */
    public function edit(Workspace $workspace): JsonResponse
    {
        $workspace->load('qualification_type');

        $selection = Criterion::getSelectionCheckbox($workspace);
        $workspace['criteria_workspace'] = $selection['criteria_workspace'];
        $workspace['criteria_workspace_dates'] = $selection['criteria']->where('field_type.code', 'date')->values()->all();

        $workspace['limit_allowed_users'] = $workspace->limit_allowed_users['quantity'] ?? null;
        $workspace->limits = [
            'limit_allowed_media_convert' => $workspace->limits['limit_allowed_media_convert'] ?? 0,
            'limit_allowed_ia_evaluations' => $workspace->limits['limit_allowed_ia_evaluations'] ?? 0,
            'limit_descriptions_jarvis' => $workspace->limits['limit_descriptions_jarvis'] ?? 0,
        ];
        $workspace->jarvis_configuration = [
            'openia_token' => $workspace->jarvis_configuration['openia_token'] ?? '',
            'openia_model' => $workspace->jarvis_configuration['openia_model'] ?? 'gpt-3.5-turbo',
            'context_jarvis' => $workspace->jarvis_configuration['context_jarvis'] ?? ''
        ];
        
        $workspace->reminders_configuration = [
            'chunk' => $workspace->reminders_configuration['chunk'] ?? 0,
            'interval' => $workspace->reminders_configuration['interval'] ?? 0,
        ];

        $workspace['is_superuser'] = auth()->user()->isA('super-user');

        $workspace['functionalities_selected'] = WorkspaceFunctionality::functionalities($workspace->id, true);
        $workspace['functionalities'] = Taxonomy::getDataForSelect('system', 'functionality');
        $workspace['qualification_types'] = Taxonomy::getDataForSelect('system', 'qualification-type');
        $workspace['subworkspaces'] = get_subworkspaces(get_current_workspace());
        //S3
        return $this->success($workspace);
    }

    /**
     * Process request to save record changes
     *
     * @param WorkspaceRequest $request
     * @param Workspace $workspace
     * @return JsonResponse
     */
    public function update(WorkspaceRequest $request, Workspace $workspace): JsonResponse
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'logo_negativo');
        $data = Media::requestUploadFile($data, 'logo_marca_agua');
        // Set constraint: limit allowed users
        $data['limits'] = [
            'limit_allowed_media_convert' => $data['limit_allowed_media_convert'] ?? null,
            'limit_allowed_ia_evaluations' => $data['limit_allowed_ia_evaluations'] ?? null,
            'limit_descriptions_jarvis' => $data['limit_descriptions_jarvis'] ?? 0,
        ];
        $data['jarvis_configuration'] = [
            'openia_token' => $data['openia_token'] ?? '',
            'openia_model' => $data['openia_model'] ?? 'gpt-3.5-turbo',
            'context_jarvis' => $data['context_jarvis'] && $data['context_jarvis'] !='null' ? $data['context_jarvis'] : ''
        ];
        $data['reminders_configuration'] = json_decode($data['reminders_configuration']);
        // dd($data);
        // // $data['reminders_configuration'] = json [
        // //     'chunk' => $data['reminders_configuration']['chunk'] ?? 0,
        // //     'interval' => $data['reminders_configuration']['interval'] ?? 0,
        // // ];
        if (($data['limit_allowed_users_type'] ?? false) && ($data['limit_allowed_users_limit'] ?? false)):

            $constraint_user['type'] = $data['limit_allowed_users_type'];
            $constraint_user['quantity'] = intval($data['limit_allowed_users_limit']);

            $data['limit_allowed_users'] = $constraint_user;
        else:
            $data['limit_allowed_users'] = null;
        endif;
        $data['dc3_configuration'] = json_decode($data['dc3_configuration']);
        // Update record in database
        $workspace->update($data);
        // Save workspace's criteria

        if ( !empty($data['criteria']) ) {
            $workspace->criterionWorkspace()->sync($data['criteria']);
        }


        // Actualizar funcionalidades

        $selected_functionality = json_decode($data['selected_functionality'], true);

        foreach($selected_functionality as $fun_id => $fun) {

            $exist = WorkspaceFunctionality::where('workspace_id', $workspace->id)->where('functionality_id', $fun_id)->first();

            if($exist) {
                if(!$fun) {
                    $exist->delete();
                }
            }
            else {
                if($fun) {
                    try {

                        DB::beginTransaction();
                        $data = array('workspace_id'=> $workspace->id, 'functionality_id'=>$fun_id);
                        WorkspaceFunctionality::create($data);

                        DB::commit();
                    } catch (\Exception $e) {
                        info($e);
                        DB::rollBack();
                        abort(errorExceptionServer());
                    }
                }
            }
        }

        cache_clear_model(WorkspaceFunctionality::class);


        \Artisan::call('modelCache:clear', array('--model' => "App\Models\Criterion"));

        return $this->success(['msg' => 'Workspace actualizado correctamente.']);
    }

    // Sub Workspaces

    public function searchSubWorkspace(Request $request)
    {
        $subworkspaces = Workspace::searchSubWorkspace($request);
        SubWorkspaceResource::collection($subworkspaces);

        return $this->success($subworkspaces);
    }

    public function getListSubworkspaceSelects()
    {
        $current_workspace = get_current_workspace();

        $active_users_count = User::onlyClientUsers()->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->where('active', 1)->count();
        $limit_allowed_users = $current_workspace->getLimitAllowedUsers();

        return $this->success(compact('active_users_count', 'limit_allowed_users'));
    }
    public function customEmails(Workspace $workspace){
        $data = WorkspaceCustomEmail::list($workspace);
        return $this->success($data);
    }
    public function showEmail(Workspace $workspace,Request $request){
        // $data = WorkspaceCustomEmail::search($workspace);
        $data = WorkspaceCustomEmail::search($workspace,$request->email_code);
        if(isset($data->data_custom['show_subworkspace_logo']) && $data->data_custom['show_subworkspace_logo']){
            $logo = $workspace->subworkspaces->whereNotNull('logo')->shuffle()->first()?->logo;
            if($logo){
                $data['image_subworkspace'] = get_media_url($logo,'s3');
            }
        }
        return view('emails.welcome_email',['data'=>$data]);
    }
    public function saveCustomEmail(Workspace $workspace,Request $request){
        $data = $request->all();
        WorkspaceCustomEmail::saveCustomEmail($workspace,$data);
        return $this->success(['message'=>'Se ha guardado correctamente']);
    }
    public function destroy(Workspace $workspace)
    {
        // \File::delete(public_path().'/'.$workspace->plantilla_diploma);

        DB::beginTransaction();

        try {
            // Rename record to avoid duplicated names

            $workspace->name = substr('deleted ' . Str::random(5) . ' ' . $workspace->name, 0, 255);
            $workspace->slug = substr('deleted-' . Str::random(5) . '-' . $workspace->slug, 0, 255);
            $workspace->active = 0;
            $workspace->save();

            $workspace->delete();

            $workspace->functionalities()->sync([]);
            $workspace->criterionWorkspace()->sync([]);

            $workspace->criteriaValue()->sync([]);
            // $workspace->criteriaValue()->delete();

            $workspace->videotecas()->delete();
            $workspace->meetings()->delete();
            $workspace->push_notifications()->delete();
            // $workspace->medias()->delete(); // don't delete

            foreach ($workspace->subworkspaces as $subworkspace) {

                // Rename record to avoid duplicated names

                $subworkspace->name = substr('deleted ' . Str::random(5) . ' ' . $subworkspace->name, 0, 255);
                $subworkspace->slug = substr('deleted-' . Str::random(5) . '-' . $subworkspace->slug, 0, 255);
                $subworkspace->active = 0;
                $subworkspace->save();

                // Rename and delete record to avoid duplicated names

                $moduleCriterionValue = CriterionValue::query()
                    ->where('id', $subworkspace->criterion_value_id)
                    ->first();
                $moduleCriterionValue->value_text = $subworkspace->name;
                $moduleCriterionValue->save();
                $moduleCriterionValue->delete();

                foreach ($subworkspace->schools as $school) {

                    foreach ($school->courses as $course) {

                        foreach ($course->topics as $topic) {

                            $topic->questions()->delete();
                            $topic->medias()->delete();
                            $topic->requirements()->delete();
                        }

                        $course->requirements()->delete();
                        $course->topics()->delete();
                    }

                    $school->courses()->delete();
                }

                $subworkspace->schools()->delete();

                foreach ($subworkspace->users as $user) {

                    $user->summary()->delete();
                    $user->summary_courses()->delete();
                    $user->summary_topics()->delete();
                    $user->benefits()->delete();
                    $user->segments()->delete();
                    $user->course_data()->delete();
                    $user->criterion_values()->sync([]);
                }

                $subworkspace->users()->delete();
            }

            $workspace->subworkspaces()->delete();

            foreach ($workspace->polls as $poll) {

                $poll->questions()->delete();
            }

            $workspace->polls()->delete();

            DB::commit();

            return $this->success(['msg' => 'Workspace eliminado correctamente.']);

        } catch (\Exception $e) {
            DB::rollback();

        }
    }

    public function editSubWorkspace(Workspace $subworkspace)
    {
        // $workspace = Workspace::where('criterion_value_id', $subworkspace->id)->first();

        $reinicio_automatico = $subworkspace->reinicios_programado;
        $subworkspace->reinicio_automatico = $reinicio_automatico['activado'] ?? false;
        $subworkspace->reinicio_automatico_dias = $reinicio_automatico['reinicio_dias'] ?? 0;
        $subworkspace->reinicio_automatico_horas = $reinicio_automatico['reinicio_horas'] ?? 0;
        $subworkspace->reinicio_automatico_minutos = $reinicio_automatico['reinicio_minutos'] ?? 0;


        $subworkspace->load('main_menu');
        $subworkspace->load('side_menu');

        $formSelects = $this->getFormSelects(true);

        $formSelects['main_menu']->each(function ($item) use ($subworkspace) {
            $item->active = $subworkspace->main_menu->where('id', $item->id)->first() !== NULL;
        });

        $formSelects['side_menu']->each(function ($item) use ($subworkspace) {
            $item->active = $subworkspace->side_menu->where('id', $item->id)->first() !== NULL;
        });

        $evaluacion = $subworkspace->mod_evaluaciones;
        $subworkspace->preg_x_ev = $evaluacion['preg_x_ev'] ?? NULL;
        $subworkspace->nota_aprobatoria = $evaluacion['nota_aprobatoria'] ?? NULL;
        $subworkspace->nro_intentos = $evaluacion['nro_intentos'] ?? NULL;

        $contact_support = $subworkspace->contact_support;
        $subworkspace->contact_phone = $contact_support['contact_phone'] ?? NULL;
        $subworkspace->contact_email = $contact_support['contact_email'] ?? NULL;
        $subworkspace->contact_schedule = $contact_support['contact_schedule'] ?? NULL;

        $subworkspace->plantilla_diploma = $subworkspace->plantilla_diploma ? get_media_url($subworkspace->plantilla_diploma) : null;

        $functionality = Taxonomy::getFirstData('system', 'functionality', 'registro-capacitacion');
        $registroCapacitacionFunctionality = WorkspaceFunctionality::query()
            ->where('workspace_id', $subworkspace->parent_id)
            ->where('functionality_id', $functionality->id)
            ->first();

        return $this->success([
            'modulo' => $subworkspace,
            'has_registro_capacitacion_functionality' => $registroCapacitacionFunctionality ? true : false,
            'main_menu' => $formSelects['main_menu'],
            'side_menu' => $formSelects['side_menu'],
            'workspace_criteria' => $formSelects['workspace_criteria']
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $main_menu = Taxonomy::where('group', 'system')->where('type', 'main_menu')
            ->select('id', 'name')
            ->where('active', ACTIVE)
            ->get();

        $qualification_types = Taxonomy::where('group', 'system')->where('type', 'qualification-type')
            ->select('id', 'name')
            ->where('active', ACTIVE)
            ->get();

        $main_menu->each(function ($item) {
            $item->active = false;
        });

        $side_menu = WorkspaceFunctionality::sideMenuApp(get_current_workspace()->id);

        // $side_menu = Taxonomy::select('id', 'name')
        //                      ->where('group', 'system')
        //                      ->where('type', 'side_menu')
        //                      ->where('active', ACTIVE);

        // #=== visible glossary only for FP ===
        // $jump_menu = (get_current_workspace()->id !== 25);
        // if($jump_menu) $side_menu->whereNotIn('name', ['Glosario']);
        // #=== visible glossary only for FP ===

        // $side_menu = $side_menu->get();

        // $side_menu->each(function ($item) {
        //     $item->active = false;
        // });



        $has_registro_capacitacion_functionality = boolval(get_current_workspace()->functionalities()->get()->where('code','registro-capacitacion')->first());
        $workspace_criteria = get_current_workspace()->criterionWorkspace->map(function ($item){
            return collect($item)->only(['id', 'name']);
        });

        $response = compact(
            'main_menu',
            'side_menu',
            'qualification_types',
            'workspace_criteria',
            'has_registro_capacitacion_functionality');

        return $compactResponse ? $response : $this->success($response);
    }

    public function storeSubWorkspace(SubWorkspaceRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');
        $subworkspace = Workspace::storeSubWorkspaceRequest($data);
        return $this->success(['msg' => 'Módulo registrado correctamente.']);
    }

    public function updateSubWorkspace(SubWorkspaceRequest $request, Workspace $subworkspace)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'plantilla_diploma');

        $subworkspace = Workspace::storeSubWorkspaceRequest($data, $subworkspace);
        return $this->success(['msg' => 'Módulo actualizado correctamente.']);
    }

    /**
     * Process request to copy record data
     *
     * @param Workspace $workspace
     * @return JsonResponse
     */
    // public function copy(Workspace $workspace): JsonResponse
    // {
    //     return $this->success([]);
    // }

    /**
     * Process request to duplicate record data
     *
     * @param Workspace $workspace
     * @return JsonResponse
     */
    public function duplicate(WorkspaceDuplicateRequest $request, Workspace $workspace): JsonResponse
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'logo_negativo');

        $new = $workspace->replicateWithRelations($data);

        return $this->success(['msg' => 'Workspace duplicado correctamente.']);
    }

    public function copy(Workspace $subworkspace)
    {
        $items = Workspace::getSchoolsForTree($subworkspace->schools);

        $items_destination = Workspace::getAvailableForTree($subworkspace);

        return $this->success(compact('items', 'items_destination'));
    }

    public function copyContent(Request $request, Workspace $subworkspace)
    {
        $selections = $request->selection_source;
        $destinations = $request->selection_destination;

        $workspace = get_current_workspace();

        $subworkspace_ids = [];

        foreach ($destinations as $destination) {

            $part = explode('_', $destination);
            $subworkspace_ids[] = $part[1];
        }

        $data = $this->buildSourceTreeSelection($selections);

        $subworkspaces = Workspace::whereIn('id', $subworkspace_ids)->get();
        $_schools = School::whereIn('id', $data['school_ids'])->get();
        $_courses = Course::whereIn('id', $data['course_ids'])->get();
        $_topics = Topic::with('questions', 'medias')->whereIn('id', $data['topic_ids'])->get();

        $prefix = '';
        // $prefix = '[DUPLICADO] ';

        foreach ($data['schools'] as $school_id => $course_ids) {

            $_school = $_schools->where('id', $school_id)->first();

            $school_data = $_school->toArray();
            $school_data['external_id'] = $_school->id;
            $school_data['name'] = $prefix . $_school->name;

            foreach ($subworkspaces as $subworkspace) {

                $school = $subworkspace->schools()->where('name', $_school->name)->first();

                if ( ! $school ) {

                    $school_position = ['position' => $subworkspace->schools()->count() + 1];

                    $school = $subworkspace->schools()->create($school_data, $school_position);
                }

                Workspace::setCoursesDuplication($course_ids['courses'], $_courses, $_topics, $school, $workspace, $prefix);
            }
        }

        return $this->success(['msg' => 'Contenido duplicado correctamente.']);
    }

    public function buildSourceTreeSelection($selections)
    {
        $schools = [];
        $school_ids = [];
        $course_ids = [];
        $topic_ids = [];

        foreach ($selections as $index => $selection) {

            $sections = explode('-', $selection);
            $data = [];

            foreach ($sections as $position => $section) {

                $part = explode('_', $section);

                $model = $part[0];
                $id = $part[1];

                ${$model.'_ids'}[$id] = $id;

                $row = [
                    'model' => $model,
                    'id' => $id,
                ];

                $data[] = $row;

                // $schools[$index][$position] = $row;
            }

            $school_id = $data[0]['id'];
            $course_id = $data[1]['id'] ?? NULL;
            $topic_id = $data[2]['id'] ?? NULL;

            if ($course_id && $topic_id) {

                $schools[$school_id]['courses'][$course_id]['topics'][] = $topic_id;

            } else {

                if ($course_id && !$topic_id) {

                    $schools[$school_id]['courses'][$course_id]['topics'] = [];

                } else {

                    if (!$course_id && !$topic_id) {

                        $schools[$school_id]['courses'] = [];
                    }
                }
            }

        }

        return compact('school_ids', 'course_ids', 'topic_ids', 'schools');
    }

    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param Workspace $workspace
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Workspace $workspace, Request $request)
    {
        $new_status = !$workspace->active;
        $data = ['active' => $new_status];

        $workspace->update($data);

        if (!$workspace->parent_id) {

            // channge status - users and admins

            User::whereHas('subworkspace', function($q) use ($workspace) {
                $q->where('parent_id', $workspace->id);
            })
            ->whereRelation('type', 'code', '<>', 'cursalab')
            ->whereNull('secret_key')
            ->update($data);

            User::query()
                ->whereRelation('type', 'code', '<>', 'cursalab')
                ->join('assigned_roles as ar', 'ar.entity_id', 'users.id')
                ->where('ar.entity_type', AssignedRole::USER_ENTITY)
                ->where('ar.scope', $workspace->id)
                ->whereNull('secret_key')
                ->update($data);

            // channge status - subworkspaces

            $workspace->subworkspaces()->update($data);
        }

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }
}
