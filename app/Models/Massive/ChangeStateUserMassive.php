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
    public $state_user_massive; //declare variable when set the instance of this class 
    public function collection(Collection $rows) {
        // state_user_massive <- 1(active) or 0(inactive)
        //Delete header
        $rows->shift();
        // process dni or email <- change state user where dni or email 
        $this->process_data($rows);
        $this->q_errors = count($this->errors);
    }
    private function process_data($rows_dni_or_email){
        foreach ($rows_dni_or_email as $dni_or_email) {
            if(!is_null($dni_or_email)){
                $this->search_user($dni_or_email);
            }
        }
    }
    private function search_user($dni_or_email){
        $user = User::where(function($q) use ($dni_or_email){
            $q->where('document',$dni_or_email)->orWhere('email',$dni_or_email);
        })->select('id')->first();
        if($user){
            $this->inactivar_usuario($user);
        }else{
            //ERRORES
            $this->errors[]= ['type'=>'Resource not found.','message'=>'Resource not found.','value'=>$dni_or_email];
        }
    }
    private function inactivar_usuario($user){
        $user->active = $this->state_user_massive;
        $user->save();
        $this->q_change_status++;
    }
    public function getStaticHeader(){
        return collect(['DNI']);
    }
}
