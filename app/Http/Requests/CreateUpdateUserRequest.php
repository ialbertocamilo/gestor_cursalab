<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUpdateUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'users' => 'required|array',
            'workspace_id' => 'required|exists:workspaces,id'
        ];
    }
    public function messages(){
        return [
            'users.required' => 'The field users is required.',
            'users.array' => 'The fieds users may be an array.',
            'workspace_id.required' => 'The field workspace_id is required.',
            'workspace_id.exists' => 'Workspace not found.'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
