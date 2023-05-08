<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmbienteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'titulo_login_app' => 'required|min:5|max:255',
            'subtitulo_login_app' => 'required|min:5|max:255',

            'form_login_transparency' => 'nullable',
            'form_login_position' => 'nullable',
            'color_primario_app' => 'nullable',
            'color_secundario_app' => 'nullable',
            
            'fondo_app' => 'nullable',
            'logo_app' => 'nullable',
            'logo_cursalab_position' => 'nullable',
            'show_blog_btn' => 'nullable',
            
            'logo_cursalab' => 'nullable',
            'completed_courses_logo' => 'nullable',
            'enrolled_courses_logo' => 'nullable',
            'diplomas_logo' => 'nullable',
        ];
    }
    public function validationData(): array
    {

        $data = [];
        if ($this->has('show_blog_btn') ) {
            $data['show_blog_btn'] = ($this->show_blog_btn == 'true' || 
                                       $this->show_blog_btn == 1 ) ? true : false;
        }

        return $this->merge($data)->all();
    }
}

