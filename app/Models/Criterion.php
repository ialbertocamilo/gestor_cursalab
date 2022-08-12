<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Criterion extends BaseModel
{

    protected $table = 'criteria';

    protected $fillable = [
        'name', 'code', 'position',
        'parent_id', 'field_id', 'validation_id',
        'show_as_parent', 'show_in_reports', 'show_in_ranking',
        'show_in_profile', 'show_in_segmentation',
        'show_in_form', 'required', 'editable_configuration',
        'editable_segmentation', 'multiple', 'active', 'description',
    ];

    public function sluggable(): array
    {
        return [
            'code' => ['source' => 'name', 'onUpdate' => true, 'unique' => true]
        ];
    }


    public function values()
    {
        return $this->hasMany(CriterionValue::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function field_type()
    {
        return $this->belongsTo(Taxonomy::class, 'field_id');
    }

    protected function getValuesForSelect($criterion_code)
    {
        return CriterionValue::whereRelation('criterion', 'code', $criterion_code)
            ->select('id', 'value_text as nombre')
            // ->where('criterion_id', $criterion->id)
            // ->when($config_id, function($q) use ($config_id){
            //     $q->where('config_id', $config_id);
            // })
            ->get();
    }

    protected function search($request)
    {
        $query = self::withCount('values');

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    protected function storeRequest($data, $model = null, $files = [])
    {
        try {

            DB::beginTransaction();

            if ($model) :

                $model->update($data);

            else:

                $model = self::create($data);

            endif;


            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

    protected function validationsOnUpdate(Criterion $criterion, array $request): array
    {
        $validations = collect();

        $type_was_changed = $this->wasFieldTypeChanged($criterion, $request);
        if ($type_was_changed['ok']) $validations->push($type_was_changed);


        $show_confirm = !($validations->where('show_confirm', false)->count() > 0);

        return [
            'validations' => $validations->toArray(),
            'title' => 'Alerta',
            'show_confirm' => $show_confirm
        ];
    }

    public function wasFieldTypeChanged($criterion, $request): array
    {
        $temp['ok'] = $criterion->field_id !== $request['field_id'];
        if (!$temp['ok']) return $temp;

        $temp['title'] = "No se puede cambiar el tipo de valor del criterio.";
        $temp['subtitle'] = "Para poder cambiar el tipo de valor del criterio es necesario quitar o cambiar el requisito en los siguientes cursos:";
        $temp['show_confirm'] = false;
        $temp['type'] = 'field_type_changed';


        return $temp;
    }


}
