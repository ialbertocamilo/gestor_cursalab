<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Rules\VerifyLimitAllowedUsers;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;

class GuestUserStoreRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->segment(3);
        // $pass = $this->isMethod('post') ? 'required' : 'nullable';

        // $piecesPass = stringConcatEqualNum([$this->document, $this->email], 4);

        if ($this->isMethod('post')) {
            $passwordRules = [
                "required", 'max:100',
                Password::min(8),
            ];
        } else {
            $passwordRules = [
                "nullable", 'max:100',
                Password::min(8)->letters()->numbers()->symbols(),

                "password_available:{$id}",
                // ->mixedCase()->uncompromised(3),

                new ContextSpecificWords($this->email),
                new ContextSpecificWords($this->document),
                new ContextSpecificWords($this->name),
                new ContextSpecificWords($this->lastname),
                new ContextSpecificWords($this->surname),
                // new RepetitiveCharacters(),
                // new SequentialCharacters(),
            ];
        }

        $rules = [
            'name' => 'required|min:3|max:255',
            'lastname' => 'required|min:2|max:255',
            'surname' => 'required|min:2|max:255',
            // 'password' => "{$pass}|max:255|password_available:{$id}",
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
            // $rules['email'] ="required|email|max:255,email,{$id},id,deleted_at,NULL" ;
        }
        return $rules;
    }

    public function validationData()
    {
        $this->mergeIfMissing(['active' => INACTIVE]);

        return $this->all();
    }
}
