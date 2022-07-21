<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VademecumStoreRequest extends FormRequest
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
        $id = $this->segment(2);

        return [
            'name' => "required|max:255|unique:vademecum,name,{$id},id,deleted_at,NULL",
            'category_id' => 'nullable',
            'subcategory_id' => 'nullable',

            'media' => 'nullable',
            'file_media' => 'nullable',
            // 'media_id' => 'required|exists:media,id,deleted_at,NULL',

            'modules' => 'nullable',

            'active' => 'required',
        ];
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('active') )
            $data['active'] = false;

        $data['category_id'] = $this->has('category')
                                ? $this->category
                                : null;

        $data['subcategory_id'] = $this->has('subcategory')
                                    ? $this->subcategory
                                    : null;

        return $this->merge($data)->all();
    }
}
