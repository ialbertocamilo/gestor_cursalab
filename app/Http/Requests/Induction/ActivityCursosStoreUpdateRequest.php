<?php

namespace App\Http\Requests\Induction;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Taxonomy;

class ActivityCursosStoreUpdateRequest extends FormRequest
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
            'titulo' => 'required|max:120',
            'description' => 'nullable',
            'position' => 'nullable',
            'active' => 'nullable',
            'qualification_type_id' => 'nullable',

            'reinicios_programado' => 'nullable',
            'mod_evaluaciones' => 'required',

            'type_id' => 'nullable',

            'topic' => 'required',
            'validateForm' => 'required',
        ];
    }

    public function validationData()
    {
        $active = ($this->active === 'true' or $this->active === true or $this->active === 1 or $this->active === '1');

        $mod_evaluaciones = $this->mod_evaluaciones ? json_decode($this->mod_evaluaciones, true) : [];
        $qualification_type_id = $this->has('qualification_type') ? $this->qualification_type : null;

        if ($mod_evaluaciones && isset($mod_evaluaciones['nota_aprobatoria']) && $qualification_type_id) {
            $qualification_type = Taxonomy::find($qualification_type_id);
            $mod_evaluaciones['nota_aprobatoria'] = calculateValueForQualification($mod_evaluaciones['nota_aprobatoria'], 20, $qualification_type->position);
        }
        $type_id = Taxonomy::getFirstData('course', 'type', 'free');

        $data['active'] = $active;
        $data['name'] = $this->titulo;
        $data['validateForm'] = !!$this->validateForm;
        $data['reinicios_programado'] = $this->reinicios_programado ? json_decode($this->reinicios_programado, true) : [];
        $data['mod_evaluaciones'] = $mod_evaluaciones;
        $data['qualification_type_id'] = $qualification_type_id;
        $data['type_id'] = $type_id?->id ?? null;

        return $this->merge($data)->all();
    }
}
