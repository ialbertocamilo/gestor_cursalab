<?php

namespace App\Models\Massive;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class ChangeStateUserMassive implements ToCollection{
    public $q_change_status=0;
    public $q_errors = 0;
    public $errors = [];
    // public $identifier = '';
    public $identificator;// declare variable when set the instance of this class - The values ​​must be user attributes.
    public $state_user_massive; //declare variable when set the instance of this class 
 
    public function collection(Collection $rows) {
        // state_user_massive <- 1(active) or 0(inactive)
        //Delete header
        $rows->shift();
        // process dni or email <- change state user where dni or email 
        $this->process_data($rows);
        $this->q_errors = count($this->errors);
    }
    private function process_data($rows){
        foreach ($rows as $row) {
            if(!is_null($row)){
                $this->search_user($row,$this->identificator);
            }
        }
    }
    private function search_user($row,$identificator){
        $user = User::where(trim($identificator),$row)->select('id')->first();
        if($user){
            $user->updateStatusUser($this->state_user_massive);
        }else{
            //ERRORES
            $this->errors[]= ['type'=>'Resource not found.','message'=>'Resource not found.','value'=>$row];
        }
    }
    private function inactivar_usuario($user){
        $user->active = $this->state_user_massive;
        $user->save();
        $termination_date = ($user->active) ? null : now();
        $user->criterion_values()->sync(['termination_date'=>$termination_date]);
        $this->q_change_status++;
    }
    public function getStaticHeader(){
        return collect(['DNI']);
    }
}
