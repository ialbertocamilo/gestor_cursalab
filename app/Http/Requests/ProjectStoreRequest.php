<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Rules\ConstraintProject;
use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreRequest extends FormRequest
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

        $multimedias = ($this->has('multimedias')) ? $this->multimedias : [];
        $files = ($this->has('files')) ? $this->files : [];

        $rules = [
            'project.indications' => 'max:255',
            'project.count_file' => [new ConstraintProject('admin',$files,$multimedias)],
        ];
        //Si es nulo se edita el registro,si no lo es se crea
        if(is_null($this->project['id']) || $this->project['id']=='null'){
            // $rules['project.course_id'] =  "required|unique:projects,course_id,active,deleted_at,NULL";
        }else{
            $rules['project.course_id'] =  'required';
            $rules['project.id'] =  'exists:projects,id';
        }
        return $rules;
    }
    public function messages()
    {
        $constraints = config('project.constraints.admin');
        return [
            'project.id.exists' => 'Error al actualizar la tarea',
            'project.course_id.required' => 'El dato "Curso" es requerido.',
            'project.course_id.unique' => 'Este curso ya tiene una tarea asignado.',
            'project.name.required' => 'El dato "Curso" es requerido.',
            'project.indications.max' => 'Las indicaciones deben ser máximo de 255 caracteres.',
            'count_file' => 'Solo se pueden subir máximo '.$constraints['max_quantity_upload_files'].' archivos.'
        ];
    }
}
