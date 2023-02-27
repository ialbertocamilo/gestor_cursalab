<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Rules\VerifyLimitAllowedUsers;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->isMethod('post') ? 'NULL' : $this->segment(2);
        $pass = $this->isMethod('post') ? 'required' : 'nullable';

        $piecesPass = stringConcatEqualNum([$this->document, $this->email], 4);
        // $passwordRules = $this->isMethod('post') ? 
        //             ['max:100', "{$pass}", 'min:8'] : 
        //             ['max:100', "{$pass}", 
        //                     Password::min(8)->mixedCase()
        //                                     ->numbers()
        //                                     ->uncompromised(3) ];
        


        $rules = [
            'name' => 'required|min:3|max:255',
            'lastname' => 'required|min:2|max:255',
            'surname' => 'required|min:2|max:255',
            'password' => $passwordRules,
            'document' => "required|min:8|unique:users,document,{$id},id,deleted_at,NULL",
            'username' => 'nullable',
            'phone_number' => 'nullable',
            'person_number' => 'nullable',

            'criterion_list_final' => 'nullable',
            'criterion_list' => 'nullable'
        ];
        if($id){
            $user = User::where('id',$id)->select('active')->first();
            if($user && $user->active != $this->active){
                $rules['active'] = [new VerifyLimitAllowedUsers($this->method())];
            }
        }
        if(!$id && $this->active){
            $rules['active'] = [new VerifyLimitAllowedUsers($this->method())];
        }
        if($this->email){
            $rules['email'] ="required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL" ;
        }
        return $rules;
    }

    public function validationData()
    {
        $this->mergeIfMissing(['active' => INACTIVE]);

        return $this->all();
    }

    public function messages()
    {
        return [
            'name.required' => 'El dato "nombre" es requerido',
            
            'password.required' => 'El dato "contraseña" es requerido',
            'password.min' => 'El dato "contraseña" debe contener al menos 8 caracteres',
            'password.max' => 'El dato "contraseña" no debe ser mayor a 100 caracteres',
            'password.not_regex' => 'El dato "contraseña" no debe ser similar a tu nro de documento y/o email',

            'document.unique' => 'Este documento ya ha sido registrado: intente con otro',
            
            'email.required' => 'El dato "correo" es requerido',
            'email.email' => 'El dato "correo" debe tener formato abc@ejemplo.com',
            'email.unique' => 'Este correo ya ha sido regitrado: intente con otro'
        ];
    }
}
