<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255',
            'username' => 'nullable|min:3|max:255',
            'identifier' => 'nullable|min:3|max:255',

            // 'key' => 'required|min:3|max:255',
            // 'secret' => 'required|min:3|max:255',

            'client_id'=>'required|min:3|max:255',
            'client_secret'=>'required|min:3|max:255',
            'account_id'=>'required|min:3|max:255',

            'service_id' => 'required',
            'plan_id' => 'required',
            'type_id' => 'required',

            'active' => 'nullable',

            'description' => 'nullable',
            'password' => 'nullable',
            'token' => 'nullable',
            'refresh_token' => 'nullable',
            'workspace_id' => 'nullable',


        ];

        return $rules;
    }

    public function validationData()
    {
        $data['workspace_id'] = get_current_workspace_indexes('id'); #añadiendo workspace
        $data['service_id'] = $this->has('service') ? $this->service : null;
        $data['plan_id'] = $this->has('plan') ? $this->plan : null;
        $data['type_id'] = $this->has('type') ? $this->type : null;
        $data['active'] = $this->has('active') ? $this->active : false;

        return $this->merge($data)->all();
    }

}
