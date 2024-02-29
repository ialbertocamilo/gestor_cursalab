<?php

namespace App\Models\Massive;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Massive;
use App\Models\Criterion;
use App\Models\Workspace;
use App\Models\UsuarioMaster;
use App\Models\CriterionValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\NationalOccupationCatalog;
use App\Http\Controllers\UsuarioController;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserMassive extends Massive implements ToCollection
{
    public $errors = [];
    public $processed_users = 0;
    public $current_workspace = null;
    private $subworkspaces = [];
    public $excelHeaders;
    public $messageInSpanish = true;
    public $rows = [];
    public $error_message = null;
    public $rows_to_activate = 0;
    private $user_states=[
        'active',
        'inactive'
    ];
    public function __construct($data = [])
    {
        $this->name_socket = $this->formatNameSocket('upload-massive', $data['number_socket'] ?? null);
        $this->percent_sent = [];
    }

    public function collection(Collection $rows)
    {
        $this->excelHeaders = $rows[0];
        //Don't count the header in the constraint, verifyConstraintMassive <- function extends from class Massive
        $this->verifyConstraintMassive('user_update_massive', count($rows) - 1);

        if (is_null($this->current_workspace)) {
            $this->current_workspace = get_current_workspace();
        }
        $this->subworkspaces = Workspace::select('id','name', 'criterion_value_id')->where('parent_id', $this->current_workspace->id)->get();
        $criteria = Criterion::query()
            ->with('field_type:id,code')
            ->where('code', '<>', 'document')
            ->select('id', 'name', 'code', 'parent_id', 'multiple', 'required', 'field_id','can_be_create')
            ->orderBy('position')
            ->get();
        //Verify statics headers
        if(!$this->headersIsComplete($rows[0]->toArray())){
            return;
        }
        //Get headers
        $headers = $this->process_header($rows[0], $criteria);

        $rows->shift();
        $this->rows = $rows;

        //        $this->sortRows();
        if (!$this->validateLimitAllowedUsers()):
            $message = config('errors.limit-errors.limit-user-allowed');
            $this->current_workspace->sendEmailByLimit();
            $this->error_message = $message;
            return;
        endif;
        $this->process_user($rows, $headers, $criteria);
        $this->current_workspace->sendEmailByLimit();
    }

    private function process_user($users, $headers, $criteria)
    {
        $count_users = count($users);
        $counter = 0;
        foreach ($users as $user) {
            $percent = round(($counter / $count_users) * 100);
            $this->sendEchoPercentEvent($percent, $this->name_socket, $this->percent_sent) && $this->percent_sent[] = $percent;
            $counter++;

            $data_users = collect();
            $data_criteria = collect();
            $headers->each(function ($obj) use ($data_criteria, $data_users, $user) {
                $value_excel = is_null($user[$obj['index']]) ? '' : trim($user[$obj['index']]);
                $value_excel = preg_replace('/\s\s+/', ' ', $value_excel);//remove multiple spaces
                if ($obj['is_criterion']) {
                    $data_criteria->push([
                        'criterion_code' => $obj['criterion_code'],
                        'criterion_id' => $obj['criterion_id'],
                        'criterion_name' => $obj['criterion_name'],
                        'required' => $obj['required'],
                        'can_be_create' => $obj['can_be_create'],
                        'value_excel' => $value_excel,
                        'index' => $obj['index'],
                    ]);
                } else {
                    $data_users->push([
                        'code' => $obj['header_static_code'],
                        'name' => $obj['name_header'],
                        'required' => $obj['header_static_required'],
                        'value_excel' => $value_excel,
                        'index' => $obj['index']
                    ]);
                }
            });
            $data_user = $this->prepare_data_user($data_users, $data_criteria, $criteria);

            if (!$data_user['has_error']) {
                $user = User::where('document', $data_user['user']['document'])->first();
                if (env('MULTIMARCA') === true) {
                    $master_user = UsuarioMaster::where('dni', $data_user['user']['document'])->first();
                    $master_user_arr = [
                        'dni' => $data_user['user']['document'],
                        'username' => isset($data_user['user']['username']) ? $data_user['user']['username']: null,
                        'email' => isset($data_user['user']['email']) && trim($data_user['user']['email']) !== '' ? $data_user['user']['email'] : null,
                        'customer_id' => ENV('CUSTOMER_ID'),
                        'created_at' => now()
                    ];
                    //Insert user and criteria
                    UsuarioMaster::storeRequest($master_user_arr, $master_user);
                }
                User::storeRequest($data_user['user'], $user, false, true);
                $this->processed_users++;
            } else {
                //set errors
                $this->errors[] = [
                    'row' => $user,
                    'errors_index' => $data_user['errors_index']
                ];
            }

        }
        cache_clear_model(User::class);
        cache_clear_model(CriterionValue::class);
        if (env('MULTIMARCA') === true)
            cache_clear_model(UsuarioMaster::class);
    }

    private function prepare_data_user($data_users, $data_criteria, $criteria)
    {
        $user = [];
        $has_error = false;
        $errors_index = [];
        $user = [];
        $email_index = null;
        $username_index = null;
        foreach ($data_users as $key => $dt) {
            ($dt['code'] == 'email') && $email_index = $dt['index'];
            ($dt['code'] == 'username') && $username_index = $dt['index'];

            if (empty($dt['value_excel']) && $dt['required'] && $dt['name']) {
                $has_error = true;
                $errors_index[] = [
                    'index' => $dt['index'],
                    'message' => ($this->messageInSpanish) ? 'El campo ' . $dt['name'] . ' es requerido.' : 'The field ' . $dt['code'] . ' is required'
                ];
                continue;
            }
            $user[$dt['code']] = $dt['value_excel'];
            if(strpos($dt['value_excel'],'=')===0){
                $has_error = true;
                $errors_index[] = [
                    'index' => $dt['index'],
                    'message' => 'No debe incluir fórmula de Excel.'
                ];
                continue;
            }
            if ($dt['code'] == 'active') {
                if(!in_array(mb_strtolower($dt['value_excel']),$this->user_states)){
                    $has_error = true;
                    $errors_index[] = [
                        'index' => $dt['index'],
                        'message' => 'Los valores para el estado son: Active o Inactive.'
                    ];
                    continue;
                }

                $user[$dt['code']] = (mb_strtolower($dt['value_excel']) == 'active') ? 1 : 0;
            }

            // No module defined

//            if (!isset($dt['module'])) {
//                $has_error = true;
//                $errors_index[] = [
//                    'index' => $dt['index'],
//                    'message' => 'El módulo es obligatorio'
//                ];
//                continue;
//            }

            // Module is defined but without a value

//            if (isset($dt['module'])) {
//
//                if (!$dt['module']) {
//                    $has_error = true;
//                    $errors_index[] = [
//                        'index' => $dt['index'],
//                        'message' => 'El módulo es obligatorio'
//                    ];
//                    continue;
//                }
//            }


            //DC3
            if($dt['code'] == 'national_occupation_id'){
                $occupation =  NationalOccupationCatalog::searchByCodeOrName(trim($dt['value_excel']))->first();
                if($occupation){
                    $user[$dt['code']] = $occupation->id;
                }else{
                    $has_error = true;
                    $errors_index[] = [
                        'index' => $dt['index'],
                        'message' => 'El valor no fue encontrado.'
                    ];
                    continue;
                }
            }
        }
        //verify username and email fields are unique
        $user_username_email = null;
        $master_username_email = null;
        if (isset($user['document'])) {
            $user_username_email = User::where(function ($q) use ($user) {
                isset($user['username']) && $q->orWhere('username', $user['username']);
                isset($user['email']) && $q->orWhere('email', $user['email']);
            })->where('document', '<>', $user['document'])->select('email', 'username')->first();

            if (env('MULTIMARCA') === true) {
                $master_username_email = UsuarioMaster::where(function ($q) use ($user) {
                    if (isset($user['username'])) {
                        $q->orWhere('username', $user['username']);
                    }
                    if (isset($user['email'])) {
                        $q->orWhere('email', $user['email']);
                    }
                })->where('dni', '<>', $user['document'])->select('email', 'username')->first();
                info(array($master_username_email));
            }

        } else {
            $has_error = true;
            $errors_index[] = [
                'index' => $dt['index'],
                'message' => ($this->messageInSpanish) ? 'El campo documento es requerido.' : 'The field document is required'
            ];
        }
        if (env('MULTIMARCA') === true) {

            if ($user_username_email || $master_username_email) {
                if (isset($user['username']) && $user['username'] != '' &&
                    !is_null($user_username_email->username) &&
                    mb_strtolower($user_username_email->username) == mb_strtolower($user['username']) ||
                    isset($user['username']) && $user['username'] != '' && !is_null($master_username_email->username)
                    && mb_strtolower($master_username_email->username) == mb_strtolower($user['username'])) {

                    $has_error = true;
                    $errors_index[] = [
                        'index' => $username_index,
                        'message' => ($this->messageInSpanish) ? 'Este username es usado por otro usuario.' : 'The field username must be unique.'
                    ];
                }


                if ($user['email'] != '' && !is_null($user_username_email->email ?? null)
                    && mb_strtolower($user_username_email->email ?? '') == mb_strtolower($user['email'])
                    || $user['email'] != '' && !is_null($master_username_email)
                    && mb_strtolower($master_username_email->email ?? '') == mb_strtolower($user['email'])) {

                    $has_error = true;
                    $errors_index[] = [
                        'index' => $email_index,
                        'message' => 'Este email es usado por otro usuario.'
                    ];
                }
            }
        } else {
            if ($user_username_email ) {
                if (isset($user['username']) && $user['username'] != '' &&
                    !is_null($user_username_email->username) &&
                    mb_strtolower($user_username_email->username) == mb_strtolower($user['username']) ) {

                    $has_error = true;
                    $errors_index[] = [
                        'index' => $username_index,
                        'message' => ($this->messageInSpanish) ? 'Este username es usado por otro usuario.' : 'The field username must be unique.'
                    ];
                }
                if ($user['email'] != '' && !is_null($user_username_email->email)
                    && mb_strtolower($user_username_email->email) == mb_strtolower($user['email'])) {

                    $has_error = true;
                    $errors_index[] = [
                        'index' => $email_index,
                        'message' => ($this->messageInSpanish) ? 'Este email es usado por otro usuario.' : 'The field email must be unique.'
                    ];
                }
            }
        }
        if (!$has_error) {
            $user['password'] = $user['document'];

        }

        $user['criterion_list'] = [];
        $user['criterion_list_final'] = [];
        foreach ($data_criteria as $dc) {
            //Validación de requerido
            if($dc['criterion_code'] == 'gender' && empty($dc['value_excel'])){
                $has_error = true;
                $errors_index[] =[
                    'index' => $dc['index'],
                    'message' => ($this->messageInSpanish) ? 'El criterio género es requerido.' : 'The field ' . $dc['criterion_code'] . ' is required.'
                ];
            }
            if (!empty($dc['value_excel'])) {
                $criterion = $criteria->where('id', $dc['criterion_id'])->first();
                $code_criterion = $criterion->field_type->code;
                if (isset($code_criterion) && $code_criterion == 'date') {
                    $dc['value_excel'] = $this->excelDateToDate($dc['value_excel']);
                    if ($dc['value_excel'] == 'invalid date') {
                        $has_error = true;
                        $errors_index[] = [
                            'index' => $dc['index'],
                            'message' => ($this->messageInSpanish) ? 'Fecha inválida.' : 'The field ' . $dc['criterion_code'] . ' is invalid date.'
                        ];
                        continue;
                    }
                }
                $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
                // if($criterion->code=='module'){
                //     $colum_name = 'external_value';
                // }
                //group exception is for UCFP only grupo -> M5::Grupo 1
                if($dc['criterion_code'] == 'grupo' && !str_contains($dc['value_excel'],'::')){
                    $dc['value_excel'] = $this->getNameWithGroupPrefix($dc['value_excel'],$data_criteria);
                }
                //cycle exception is for ucpf only
                if($dc['criterion_code'] == 'cycle'){
                    $dc['value_excel'] = $this->getCycles($dc['value_excel']);
                }
                if (is_array($dc['value_excel'])) {
                    foreach ($dc['value_excel'] as $key => $value_excel) {
                        $criterion_value = $this->getCriterionValueId($colum_name,$dc,$criterion,$value_excel,$code_criterion);
                        if($criterion_value['has_error']){
                            $has_error = $criterion_value['has_error'];
                            $errors_index[] = $criterion_value['info_error'];
                            continue;
                        }
                        $user['criterion_list'][$dc['criterion_code']][] =  $criterion_value['criterion_value']->id;
                        $user['criterion_list_final'][] =  $criterion_value['criterion_value']->id;
                    }
                }else{
                    $criterion_value = $this->getCriterionValueId($colum_name,$dc,$criterion,$dc['value_excel'],$code_criterion);
                    if($criterion_value['has_error']){
                        $has_error = $criterion_value['has_error'];
                        $errors_index[] = $criterion_value['info_error'];
                        continue;
                    }
                    $user['criterion_list'][$dc['criterion_code']] = $criterion_value['criterion_value']->id;
                    $user['criterion_list_final'][] =  $criterion_value['criterion_value']->id;
                }
            }
        }

        return compact('has_error', 'user', 'errors_index');
    }
    private function getCriterionValueId($colum_name,$dc,$criterion,$value_excel,$code_criterion){
        $has_error = false;
        if (strpos($value_excel, "=") === 0) {
            $has_error = true;
            return [
                'has_error'=>true,
                'info_error'=>[
                    'index' => $dc['index'],
                    'message' => 'No se puede usar fórmulas de excel.'
                ],
            ];
        }
        $criterion_value = CriterionValue::where('criterion_id', $criterion->id)->where($colum_name, $value_excel)->first();
        if(!$dc['can_be_create'] && !$criterion_value && $code_criterion != 'date'){
            $has_error = true;
            return [
                'has_error'=>true,
                'info_error'=>[
                    'index' => $dc['index'],
                    'message' => 'Solo puedes subir valores que han sido registrados. Revisa la ortografía y vuelve a intentarlo.'
                ],
            ];
        }
        if ($dc['criterion_code'] == 'module' && (!$criterion_value || !$this->subworkspaces->where('criterion_value_id', $criterion_value?->id)->first())) {
            $has_error = true;
            return [
                'has_error'=>$has_error,
                'info_error'=>[
                    'index' => $dc['index'],
                    'message' => ($this->messageInSpanish) ? 'Colocar un módulo existente.' : 'The field ' . $dc['criterion_code'] . ' not exist.'
                ],
            ];
        }

        if (!$criterion_value) {
            // $has_error = true;
            // $errors_index[] = $dc['index'];
            $criterion_value = new CriterionValue();
            $criterion_value[$colum_name] = $value_excel;
            $criterion_value['value_text'] = $value_excel;
            // $criterion_value->value_text = $dc['value_excel']; //Falta cambiar
            // $criterion_value->value_boolean = ($code_criterion == 'boolean');
            $criterion_value->criterion_id = $criterion->id;
            $criterion_value->active = 1;
            $criterion_value->save();
            // $criterion_value->workspaces()->syncWithoutDetaching([ $this->current_workspace->id]);
        }
        $workspace_value = DB::table('criterion_value_workspace')->where([
            'workspace_id' => $this->current_workspace->id,
            'criterion_value_id' => $criterion_value->id
        ])->first();
        if (!$workspace_value) {
            DB::table('criterion_value_workspace')->insert([
                'workspace_id' => $this->current_workspace->id,
                'criterion_value_id' => $criterion_value->id
            ]);
        }
        return [
            'has_error'=>$has_error,
            'criterion_value'=>$criterion_value
        ];
    }
    private function getCycles($cycle_name){
        return match (strtolower($cycle_name)) {
            'ciclo 2' => ['ciclo 1','ciclo 2'],
            'ciclo 3' => ['ciclo 1','ciclo 2','ciclo 3'],
            'ciclo 4' => ['ciclo 1','ciclo 2','ciclo 3','ciclo 4'],
            default => $cycle_name
        };
    }
    private function getNameWithGroupPrefix($value_name,$data_criteria){
        $name_module = $data_criteria->where('criterion_code','module')->first()['value_excel'];
        if($name_module){
            $subworkspace = $this->subworkspaces->where('name',$name_module)->first();
            $prefix = $this->getPrefix($subworkspace?->name);
            if($prefix){
                return $prefix.'::'.$value_name;
            }
        }
        return $value_name;
    }
    //cambiar prefijos <- M , Ik, FAPE , Admin
    private function getPrefix($module){
        return match ($module) {
            'Mifarma' => 'MF',
            'Inkafarma' => 'IK',
            'FAPE Masivos' => 'FP',
            'Administrativos FAPE' => 'FP',
            default => false
        };
    }
    private function process_header($headers, $criteria)
    {
        $data_excel = collect();
        $headers->map(function ($header_excel, $index) use ($data_excel, $criteria) {
            // is static header or header criterion
            $data = $this->is_static_header(mb_strtoupper($header_excel));
            $criterion = null;
            if (!$data) {
                $criterion = $criteria->where('name', trim($header_excel))->first();
            }
            $data_excel->push([
                'is_criterion' => boolval($criterion),
                'criterion_code' => $criterion ? $criterion->code : null,
                'criterion_id' => $criterion ? $criterion->id : null,
                'header_static_code' => isset($data['code']) ? $data['code'] : null,
                'header_static_required' => isset($data['required']) ? $data['required'] : true,
                'criterion_name' => $criterion ? $criterion->name : null,
                'required' => $criterion ? $criterion->required : true,
                'can_be_create' => $criterion ? $criterion->can_be_create : true,
                'name_header' => mb_strtoupper(trim($header_excel)),
                'index' => $index,
            ]);
        });
        return $data_excel;
    }

    private function is_static_header($value)
    {
        $static_headers = $this->getStaticHeaders();
        return $static_headers->where('header_name', $value)->first();
    }
    private function headersIsComplete($excel_headers):bool {
        $static_headers = self::getStaticHeaders()->where('required',true)->toArray();
        $isComplete=true;
        foreach ($static_headers as $header) {
            if(!in_array(strtolower($header['header_name']),$excel_headers) && !in_array(strtoupper($header['header_name']),$excel_headers)){
                $this->error_message = 'La columna '.$header['header_name']. ' dentro de la plantilla es requerida.';
                $isComplete = false;
            }
        }
        return $isComplete;
    }
    public function getStaticHeaders()
    {
        $has_DC3_functionality = boolval(get_current_workspace()->functionalities()->get()->where('code','dc3-dc4')->first());
        if (env('MULTIMARCA') === true) {
            $staticHeaders = collect([
                ['required' => true, 'header_name' => 'ESTADO', 'code' => 'active'],
                // ['required' => true, 'header_name' => 'NOMBRE COMPLETO', 'code' => 'fullname'],
                // ['required' => false, 'header_name' => 'USERNAME', 'code' => 'username'],
                ['required' => true, 'header_name' => 'NOMBRES', 'code' => 'name'],
                ['required' => true, 'header_name' => 'APELLIDO PATERNO', 'code' => 'lastname'],
                ['required' => false, 'header_name' => 'APELLIDO MATERNO', 'code' => 'surname'],
                ['required' => true, 'header_name' => 'DOCUMENTO', 'code' => 'document'],
                ['required' => false, 'header_name' => 'NÚMERO DE TELÉFONO', 'code' => 'phone_number'],
                // ['required' => false, 'header_name' => 'NÚMERO DE PERSONA COLABORADOR', 'code' => 'person_number'],
                ['required' => false, 'header_name' => 'EMAIL', 'code' => 'email']
            ]);
            if($has_DC3_functionality){
                $staticHeaders->splice(5, 0, ['required' => true, 'header_name' => 'CURP', 'code' => 'curp']);
                $staticHeaders->splice(6, 0, ['required' => true, 'header_name' => 'OCUPACIÓN', 'code' => 'national_occupation_id']);
            }
        }else {

            $staticHeaders = collect([
                ['required' => true, 'header_name' => 'ESTADO', 'code' => 'active'],
                ['required' => false, 'header_name' => 'NOMBRE COMPLETO', 'code' => 'fullname'],
                ['required' => false, 'header_name' => 'USERNAME', 'code' => 'username'],
                ['required' => true, 'header_name' => 'NOMBRES', 'code' => 'name'],
                ['required' => true, 'header_name' => 'APELLIDO PATERNO', 'code' => 'lastname'],
                ['required' => false, 'header_name' => 'APELLIDO MATERNO', 'code' => 'surname'],
                ['required' => true, 'header_name' => 'DOCUMENTO', 'code' => 'document'],
                ['required' => false, 'header_name' => 'NÚMERO DE TELÉFONO', 'code' => 'phone_number'],
                ['required' => false, 'header_name' => 'NÚMERO DE PERSONA COLABORADOR', 'code' => 'person_number'],
                ['required' => false, 'header_name' => 'EMAIL', 'code' => 'email']
            ]);
            if($has_DC3_functionality){
                $staticHeaders->splice(7, 0, [['required' => true, 'header_name' => 'CURP', 'code' => 'curp']]);
                $staticHeaders->splice(8, 0, [['required' => true, 'header_name' => 'OCUPACIÓN', 'code' => 'national_occupation_id']]);
            }
        }
        return $staticHeaders;
    }

    private function excelDateToDate($fecha)
    {
        $fecha = trim($fecha);
        try {
            if (_validateDate($fecha, 'Y-m-d')) {
                return $fecha;
            }
            if (_validateDate($fecha, 'Y/m/d') || _validateDate($fecha, 'd/m/Y') || _validateDate($fecha, 'd-m-Y')) {
                // return date("d/m/Y",$fecha);
                return Carbon::parse($fecha)->format('Y-m-d');
            }
            $php_date = $fecha - 25569;
            $date = date("Y-m-d", strtotime("+$php_date days", mktime(0, 0, 0, 1, 1, 1970)));
            return $date;
        } catch (\Throwable $th) {
            try {
                if(strtotime($fecha)){
                    return Carbon::parse(strtotime($fecha))->format('Y-m-d');
                }else{
                    return 'invalid date';
                }
            } catch (\Throwable $th) {
                return 'invalid date';
            }
        }
    }

    private function validateLimitAllowedUsers(): bool
    {
        $current_workspace = get_current_workspace();

        $workspace_limit = $current_workspace->getLimitAllowedUsers();

        if (!$workspace_limit)
            return true;

        $sub_workspaces_names = $current_workspace->subworkspaces->pluck('name')
            ->map(fn($value) => mb_strtolower($value))
            ->toArray();

        $rows = $this->getRowsByHeader('Módulo');
        $rows_filtered_by_sub_workspace = array_filter($rows, fn($value) => in_array(mb_strtolower(trim($value)), $sub_workspaces_names));
        $rows = $this->getRowsByHeader('ESTADO');
        $rows_to_activate = array_filter($rows, fn($value) => strtolower(trim($value)) === 'active');
        $rows_to_inactivate = array_filter($rows, fn($value) => strtolower(trim($value)) === 'inactive');


        $documents_to_inactivate = array_intersect(array_keys($rows_to_inactivate), array_keys($rows_filtered_by_sub_workspace));
        $documents_to_activate = array_intersect(array_keys($rows_to_activate), array_keys($rows_filtered_by_sub_workspace));

        $users_to_activate_count = User::onlyClientUsers()
            ->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->select('id', 'document', 'subworkspace_id', 'active')
            ->whereIn('document', $documents_to_activate)->where('active', INACTIVE)->count();

        $users_to_inactivate_count = User::onlyClientUsers()
            ->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->select('id', 'document', 'subworkspace_id', 'active')
            ->whereIn('document', $documents_to_inactivate)->where('active', ACTIVE)->count();


        $users_active_count = User::onlyClientUsers()
            ->whereRelation('subworkspace', 'parent_id', $current_workspace->id)
            ->select('id', 'document', 'subworkspace_id', 'active')
            ->where('active', ACTIVE)->count();

        $users_to_create_activated = array_diff(
            $documents_to_activate,
            User::onlyClientUsers()->whereRelation('subworkspace', 'parent_id', $current_workspace->id)->pluck('document')->toArray()
        );

        $users_to_create_activated_count = count($users_to_create_activated);

        $this->rows_to_activate = $users_to_activate_count + $users_to_create_activated_count;

        $workspace_limit = $current_workspace->getLimitAllowedUsers();
        $validation = ($users_active_count + ($users_to_activate_count - $users_to_inactivate_count) + $users_to_create_activated_count) <= $workspace_limit;

        // dd(compact('users_active_count', 'users_to_activate_count', 'users_to_create_activated', 'workspace_limit', 'validation'));
//        return false;
        return $validation;
    }

    public function getRowsByHeader($header_to_find)
    {
        $result = [];
        $headers = $this->excelHeaders;
        $rows = $this->rows;

        $document_row_index = array_search('DOCUMENTO', $headers->all());

        $header_founded_index = array_search($header_to_find, $headers->all());

        if ($header_founded_index !== false && $document_row_index !== false):

            $result = $rows->pluck($header_founded_index, $document_row_index)->toArray();

        endif;

        return $result;
    }

    private function sortRows()
    {
        $criteria = Criterion::query()
            ->with([
                'field_type:id,code'
            ])
            ->where('code', '<>', 'document')
            ->select('id', 'name', 'code', 'parent_id', 'multiple', 'required', 'field_id','can_be_create')
            ->orderBy('position')
            ->get();
        $headers = $this->process_header($this->excelHeaders, $criteria);

        $rows = collect();
        foreach ($this->rows as $row) {
            foreach ($headers as $header) {

            }
        }

        return collect();
    }
}
