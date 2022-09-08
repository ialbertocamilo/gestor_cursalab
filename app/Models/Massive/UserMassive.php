<?php

namespace App\Models\Massive;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Criterion;
use App\Models\CriterionValue;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\UsuarioController;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserMassive implements ToCollection
{
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
                if ($obj['is_criterion']) {
                    $data_criteria->push([
                        'criterion_code' => $obj['criterion_code'],
                        'criterion_id' => $obj['criterion_id'],
                        'criterion_name' => $obj['criterion_name'],
                        'required' => $obj['required'],
                        'value_excel'=>trim($user[$obj['index']]),
                        'index' => $obj['index'],
                    ]);
                } else {
                    $data_users->push([
                        'code' => $obj['header_static_code'],
                        'value_excel'=>trim($user[$obj['index']]),
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
            if(empty($dt['value_excel'])){
                $has_error = true;
                $errors_index[] = $dt['index'];
                continue;
            }
            $user[$dt['code']] = $dt['value_excel'];
            if($dt['code']=='active'){
                $user[$dt['code']] = ($dt['value_excel'] == 'Active') ? 1 : 0;
            }
        }
        try {
            $user['password'] =  $user['document'];

        } catch (\Throwable $th) {
           dd($user,$data_users);
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
                if($criterion->code=='module'){
                    $colum_name = 'external_value';
                }
                $criterion_value = CriterionValue::where('criterion_id',$criterion->id)->where($colum_name,$dc['value_excel'])->first();
                if(!$criterion_value){
                    // $has_error = true;
                    // $errors_index[] = $dc['index'];
                    $criterion_value = new CriterionValue();
                    $criterion_value[$colum_name] = $dc['value_excel'];
                    // $criterion_value->value_text = $dc['value_excel']; //Falta cambiar
                    // $criterion_value->value_boolean = ($code_criterion == 'boolean');
                    $criterion_value->criterion_id = $criterion->id;
                    $criterion_value->active = 1;
                    $criterion_value->save();
                    $criterion_value->workspaces()->syncWithoutDetaching([$this->current_workspace->id]);
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
                'criterion_name' => $criterion ? $criterion->name : null,
                'required' => $criterion ? $criterion->required : true,
                'name_header' => mb_strtoupper(trim($header_excel)),
                'index' => $index,
            ]);
        });
        return $data_excel;
    }
    private function is_static_header($value){
        $static_headers = collect([
            ['header_name'=>'ESTADO','code'=>'active'],
            ['header_name'=>'Username','code'=>'username'],
            ['header_name'=>'NOMBRE COMPLETO','code'=>'fullname'],
            ['header_name'=>'NOMBRES','code'=>'name'],
            ['header_name'=>'APELLIDO PATERNO','code'=>'lastname'],
            // ['header_name'=>'APELLIDO MATERNO','code'=>'surname'],
            ['header_name'=>'DOCUMENTO','code'=>'document'],
            ['header_name'=>'NÚMERO DE TELÉFONO','code'=>'phone_number'],
            ['header_name'=>'NÚMERO DE PERSONA COLABORADOR','code'=>'person_number'],
            ['header_name'=>'EMAIL','code'=>'email']
        ]);
        return $static_headers->where('header_name',$value)->first();
    }
    private function excelDateToDate($fecha, $rows = 0, $i = 0)
    {
        if(_validateDate($fecha,'Y-m-d')){
            return $fecha;
        }
        if(_validateDate($fecha,'Y-m-d')){
            // return date("d/m/Y",$fecha);
            return Carbon::parse($fecha)->format('d/m/Y');
        }
        $php_date =  $fecha - 25569;
        $date = date("Y-m-d", strtotime("+$php_date days", mktime(0, 0, 0, 1, 1, 1970)));
        return $date;
    }
}
