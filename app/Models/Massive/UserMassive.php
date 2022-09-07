<?php

namespace App\Models\Massive;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\UsuarioController;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserMassive implements ToCollection
{
    public $errors = [];
    public $processed_users = 0;
    public function collection(Collection $rows){
        $user =  new UsuarioController();
        $criteria = $user->getFormSelects(true);
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
                        'index' => $obj['index']
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
        }
        $user['criterion_list'] = [];
        foreach ($data_criteria as $dc) {
            $criterion = $criteria->where('id',$dc['criterion_id'])->first();
            $criterion_value_id = $criterion->values->where('value_text',$dc['value_excel'])->first();
            if(!$criterion_value_id && $dc['required']){
                $has_error = true;
                $errors_index[] = $dc['index'];
            }
            $user['criterion_list'][$dc['criterion_code']] = $criterion_value_id->id;
        }
        $user['criterion_list_final'] = [];
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
                'index' => $index
            ]);
            // 'date' => $tc->type  == 'Fecha' ? 1 : 0,
            // 'type' => $tc->data_type,
        });
        return $data_excel;
    }
    private function is_static_header($value){
        $static_headers = collect([
            ['header_name'=>'NOMBRES','code'=>'name'],
            ['header_name'=>'APELLIDO PATERNO','code'=>'lastname'],
            ['header_name'=>'APELLIDO MATERNO','code'=>'surname'],
            ['header_name'=>'IDENTIFICADOR','code'=>'document'],
            ['header_name'=>'EMAIL','code'=>'email']
        ]);
        return $static_headers->where('header_name',$value)->first();
    }
}
