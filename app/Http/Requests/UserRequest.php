<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $id = $this->isMethod('post') ? 'NULL' : $this->segment(3);
        $pass = $this->isMethod('post') ? 'required' : 'nullable';

        $rules = [
            'name' => 'required|max:255',
            'lastname' => 'required|max:255',
            'surname' => 'nullable|max:255',
            'alias' => 'nullable|max:255',
            
            'username' => 'nullable|max:255',
            'email' => "required|email|max:255|unique:users,email,{$id},id,deleted_at,NULL",
            'password' => "{$pass}|max:255",
            
            'gender_id' => 'nullable',
            'birthdate' => 'nullable',

            'type_id' => 'required',
            'area_id' => 'nullable',
            'job_position_id' => 'nullable',
            
            'country_id' => 'nullable',
            'phone' => 'nullable',
            'telephone' => 'nullable',

            'document_type_id' => 'nullable',
            'document_number' => 'nullable',
            'ruc' => 'nullable',

            'description' => 'nullable|max:3000',

            'active' => 'nullable',

            'avatar' => 'nullable',
            
            'roles' => 'nullable',
            'accounts' => 'nullable',

            // 'profile' => 'nullable',
            // 'profile_id' => 'nullable',

            // 'isotipo' => 'nullable',
            // 'isotipo_id' => 'nullable',
        ];

        return $rules;
    }

    public function validationData()
    {
        $this->mergeIfMissing(['active' => INACTIVE]);

        return $this->all();
    }
}
