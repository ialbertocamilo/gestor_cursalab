<?php

namespace App\Http\Requests;

use App\Rules\ConstraintProject;
use Illuminate\Foundation\Http\FormRequest;

class ProjectUserRequest extends FormRequest
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
        $files = ($this->has('files')) ? $this->files : [];
        $upload_files = [];
        foreach ($files as $key => $file) {
            $upload_files=$file;
        }
        return [
            'count_file' => [new ConstraintProject('user',$upload_files,[])],
        ];
    }
}
