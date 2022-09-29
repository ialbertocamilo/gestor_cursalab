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
        $this->processData($rows);
        $this->q_errors = count($this->errors);
    }
    private function processData($rows){
        foreach ($rows->toArray() as $row) {
            if(!is_null($row)){
                $user_identifier = is_array($row) ? $row[0] : $row;
                $termination_date = is_array($row) ? $row[1] : null;
                if($user_identifier){
                    $this->changeStatusUser($user_identifier,$termination_date,$this->identificator);
                }
            }
        }
    }
    private function changeStatusUser($user_identifier,$termination_date,$identificator){
        $user = User::where(trim($identificator),$user_identifier)->first();
        if($user){
            $user->updateStatusUser($this->state_user_massive,$termination_date);
            $this->q_change_status++;
        }else{
            //ERRORES
            $this->errors[]= ['type'=>'Resource not found.','message'=>'Resource not found.','value'=>$user_identifier];
        }
    }
    public function getStaticHeader(){
        return collect(['DNI']);
    }
}
