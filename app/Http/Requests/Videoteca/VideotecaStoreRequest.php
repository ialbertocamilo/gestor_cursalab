<?php

namespace App\Http\Requests\Videoteca;

use Illuminate\Foundation\Http\FormRequest;

class VideotecaStoreRequest extends FormRequest
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
        return [
            'title' => 'required|max:100',
            'description' => 'nullable',
            'category_id' => 'required',
            'active' => 'required',


            'media_type' => 'nullable',
            'modules' => 'nullable',
            'tags' => 'nullable',
            'media_video' => 'nullable',

            // 'media' => 'nullable',
            // 'media_id' => 'nullable',

            // 'preview' => 'nullable',
            // 'preview_id' => 'nullable',

            'media' => 'nullable',
            'file_media' => 'nullable',

            'preview' => 'nullable',
            'file_preview' => 'nullable',

        ];
    }

    public function validationData()
    {
        $data = [];

        if ( ! $this->has('estado') )
            $data['estado'] = false;

        return $this->merge($data)->all();
    }
}
