<?php

namespace App\Models\Massive;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Criterion;
use App\Models\CriterionValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\UsuarioController;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserMassive implements ToCollection{
    public $errors = [];
    public $processed_users = 0;
    public $current_workspace = null;
    public function collection(Collection $rows){
        $user =  new UsuarioController();
        // $criteria = $user->getFormSelects(true);
        $this->current_workspace = get_current_workspace();
        $current_workspace = $this->current_workspace;
        $criteria = Criterion::query()
            ->with([
                // 'values' => function ($q) use ($current_workspace) {
                //     $q->with('parents:id,criterion_id,value_text')
                //         // ->whereHas('workspaces', function ($q2) use ($current_workspace) {
                //         //     $q2->where('id', $current_workspace->id);
                //         // })
                //         ->select('id', 'criterion_id', 'exclusive_criterion_id', 'parent_id',
                //             'value_text');
                // },
                'field_type:id,code'
            ])
            // ->whereRelation('workspaces', 'id', $current_workspace?->id)
            ->select('id', 'name', 'code', 'parent_id', 'multiple', 'required','field_id')
            ->orderBy('position')
            ->get();
        //obtenemos las cabezeras
        $headers = $this->process_header($rows[0],$criteria);
        $rows->shift();
        $this->process_user($rows,$headers,$criteria);
    }
    private function process_user($users,$headers,$criteria){
        
        foreach ($users as $user) {
            $data_users = collect();
            $data_criteria = collect();
            $headers->each(function ($obj) use ($data_criteria, $data_users, $user) {
                $value_excel = is_null($user[$obj['index']]) ? '' : trim($user[$obj['index']]);
                if ($obj['is_criterion']) {
                    $data_criteria->push([
                        'criterion_code' => $obj['criterion_code'],
                        'criterion_id' => $obj['criterion_id'],
                        'criterion_name' => $obj['criterion_name'],
                        'required' => $obj['required'],
                        'value_excel'=>$value_excel,
                        'index' => $obj['index'],
                    ]);
                } else {
                    $data_users->push([
                        'code' => $obj['header_static_code'],
                        'required'=>$obj['header_static_required'],
                        'value_excel'=>$value_excel,
                        'index' => $obj['index']
                    ]);
                }
            });
            $data_user = $this->prepare_data_user($data_users,$data_criteria,$criteria);
            if(!$data_user['has_error']){
                //Insert user and criteria
                $user = User::where('document',$data_user['user']['document'])->first();
                User::storeRequest($data_user['user'],$user);
                $this->processed_users ++;
            }else{
                //set errors
                $this->errors[]=[
                    'row'=>$user,
                    'errors_index'=>$data_user['errors_index']
                ];
            }
        }
    }
    private function prepare_data_user($data_users,$data_criteria,$criteria){
        $user = [];
        $has_error = false;
        $errors_index = [];
        $user = [];
        foreach ($data_users as $key => $dt) {
            if(empty($dt['value_excel']) && $dt['required']){
                $has_error = true;
                $errors_index[] = $dt['index'];
                continue;
            }
            $user[$dt['code']] = $dt['value_excel'];
            if($dt['code']=='active'){
                $user[$dt['code']] = ($dt['value_excel'] == 'Active') ? 1 : 0;
            }
        }
        if(!$has_error){
            $user['password'] =  $user['document'];
        }

        $user['criterion_list'] = [];
        foreach ($data_criteria as $dc) {
            //Validación de requerido
            if(!empty($dc['value_excel'])){
                $criterion = $criteria->where('id',$dc['criterion_id'])->first();
                $code_criterion = $criterion->field_type->code;
                if(isset($code_criterion) && $code_criterion =='date'){
                    $dc['value_excel'] =  $this->excelDateToDate($dc['value_excel']);
                }
                $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
                // if($criterion->code=='module'){
                //     $colum_name = 'external_value';
                // }
                $criterion_value = CriterionValue::where('criterion_id',$criterion->id)->where($colum_name,$dc['value_excel'])->first();
                if(!$criterion_value){
                    // $has_error = true;
                    // $errors_index[] = $dc['index'];
                    $criterion_value = new CriterionValue();
                    $criterion_value[$colum_name] = $dc['value_excel'];
                    $criterion_value['value_text'] = $dc['value_excel'];
                    // $criterion_value->value_text = $dc['value_excel']; //Falta cambiar
                    // $criterion_value->value_boolean = ($code_criterion == 'boolean');
                    $criterion_value->criterion_id = $criterion->id;
                    $criterion_value->active = 1;
                    $criterion_value->save();
                    // $criterion_value->workspaces()->syncWithoutDetaching([ $this->current_workspace->id]);
                }
                $workspace_value = DB::table('criterion_value_workspace')->where([
                    'workspace_id'=> $this->current_workspace->id,
                    'criterion_value_id'=>$criterion_value->id
                ])->first();
                if(!$workspace_value){
                    DB::table('criterion_value_workspace')->insert([
                        'workspace_id'=> $this->current_workspace->id,
                        'criterion_value_id'=>$criterion_value->id
                    ]);
                }
                $user['criterion_list'][$dc['criterion_code']] = $criterion_value->id;
            }
        }
        $user['criterion_list_final'] = $user['criterion_list'];
        return compact('has_error','user','errors_index');
    }
    private function process_header($headers,$criteria){
        $data_excel = collect();
        $headers->map(function ($header_excel, $index) use ($data_excel, $criteria) {
            // is static header or header criterion
            $data = $this->is_static_header(mb_strtoupper($header_excel));
            $criterion = null;
            if(!$data){
                $criterion = $criteria->where('name',trim($header_excel))->first();
            }
            $data_excel->push([
                'is_criterion' => boolval($criterion),
                'criterion_code' => $criterion ? $criterion->code : null,
                'criterion_id' => $criterion ? $criterion->id : null,
                'header_static_code'=> isset($data['code']) ? $data['code'] : null,
                'header_static_required'=> isset($data['required']) ? $data['required'] : true,
                'criterion_name' => $criterion ? $criterion->name : null,
                'required' => $criterion ? $criterion->required : true,
                'name_header' => mb_strtoupper(trim($header_excel)),
                'index' => $index,
            ]);
        });
        return $data_excel;
    }
    private function is_static_header($value){
        $static_headers = $this->getStaticHeaders();
        return $static_headers->where('header_name',$value)->first();
    }
    public function getStaticHeaders(){
        return collect([
            ['required'=>true,'header_name'=>'ESTADO','code'=>'active'],
            ['required'=>true,'header_name'=>'USERNAME','code'=>'username'],
            ['required'=>true,'header_name'=>'NOMBRE COMPLETO','code'=>'fullname'],
            ['required'=>true,'header_name'=>'NOMBRES','code'=>'name'],
            ['required'=>true,'header_name'=>'APELLIDO PATERNO','code'=>'lastname'],
            ['required'=>true,'header_name'=>'APELLIDO MATERNO','code'=>'surname'],
            ['required'=>true,'header_name'=>'DOCUMENTO','code'=>'document'],
            ['required'=>false,'header_name'=>'NÚMERO DE TELÉFONO','code'=>'phone_number'],
            ['required'=>true,'header_name'=>'NÚMERO DE PERSONA COLABORADOR','code'=>'person_number'],
            ['required'=>false,'header_name'=>'EMAIL','code'=>'email']
        ]);
    }
    private function excelDateToDate($fecha, $rows = 0, $i = 0)
    {
        if(_validateDate($fecha,'Y-m-d') || _validateDate($fecha,'d-m-Y')){
            return $fecha;
        }
        if(_validateDate($fecha,'Y/m/d') || _validateDate($fecha,'d/m/Y')){
            // return date("d/m/Y",$fecha);
            return Carbon::parse($fecha)->format('d/m/Y');
        }
        $php_date =  $fecha - 25569;
        $date = date("Y-m-d", strtotime("+$php_date days", mktime(0, 0, 0, 1, 1, 1970)));
        return $date;
    }
}
