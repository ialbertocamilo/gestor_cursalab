<?php

namespace App\Models;

use App\Imports\VademecumImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Vademecum extends Model
{
    use SoftDeletes;

    protected $table = 'vademecum';

    protected $fillable = [
        'name', 'category_id', 'subcategory_id', 'media_id', 'active'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * Generate audits tags
     * @return string[]
     */
    public function generateTags(): array
    {
        return [
            'modelo_independiente',
        ];
    }

    /*

        Relationships

    --------------------------------------------------------------------------*/


    public function modules()
    {
        return $this->belongsToMany(
            Abconfig::class,
            'vademecum_module',
            'vademecum_id',
            'module_id'
        );
    }

    public function category()
    {
        return $this->belongsTo(Taxonomia::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Taxonomia::class, 'subcategory_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function actions()
    {
        return $this->morphMany(UsuarioAccion::class, 'model');
    }

    /*

        Attributes

    --------------------------------------------------------------------------*/


    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = (
            $value==='true' OR
            $value === true OR
            $value === 1 OR
            $value === '1'
        );
    }

    /*

        Methods

    --------------------------------------------------------------------------*/


    public function incrementAction($type_id, $quantity = 1)
    {
        $user_id = auth()->user()->id;

        $row = $this->actions()
                    ->firstOrCreate(
                        compact('user_id', 'type_id')
                    );

        $row->increment('score', $quantity);

        return $row;
    }

    protected function search($request, $api = false, $paginate = 20)
    {
        $relationships = ['modules', 'category', 'subcategory', 'media'];

        if ($api) {

            $relationships = [
                'modules' => function ($q) use ($request) {
                    if ($request->module_id)
                        $q->where('id', $request->module_id);
                },
                'subcategory',
                'category',
                'media',
            ];
        }

        $query = Vademecum::with($relationships);

        if ($request->category_id)
            $query->where('category_id', $request->category_id);

        if ($request->subcategory_id) {

            $query->whereIn(
                'subcategory_id',
                explode(',', $request->subcategory_id)
            );
        }

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        if ($request->modulo_id)
            $query->whereHas('modulos', function ($q) use ($request) {
                $q->where('id', $request->modulo_id);
            });

        if ($request->active)
            $query->where('active', $request->active);


        if ($api) {

            $query->orderBy('name', 'ASC');

        } else {

            $field = $request->sortBy ?? 'name';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

            $query->orderBy($field, $sort);
        }

        $vademecum = $query->paginate($paginate);

        return $vademecum;
    }

    protected function searchCategories($request, $api = false, $paginate = 20)
    {
        $query = Vademecum::query();

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        $categorias = $query->paginate($paginate);

        return $categorias;
    }

    protected function storeRequest($data, $elemento = null)
    {
        try {

            DB::beginTransaction();

            // $taxonomia = $this->prepareTaxonomy($data, 'category_id', 'categoria');
            // $data['category_id'] = $taxonomia->id ?? NULL;


            if ($elemento) :

                $elemento->update($data);

                $message = 'Registro actualizado correctamente';

            else :

                $elemento = $this->create($data);

                $message = 'Registro creado correctamente';

            endif;

            $elemento->modulos()->sync($data['modulos']);

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            info($e);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'success', 'message' => $message];
    }

    public function prepareTaxonomy($row, $key, $type)
    {
        $taxonomia = NULL;

        if (!empty($row[$key])) :

            $value = $row[$key];

            $taxonomia = $this->getOrCreateTaxonomyByValue($type, $value);

        endif;

        return $taxonomia;
    }

    public function getOrCreateTaxonomyByValue($type, $value)
    {
        $value = trim($value);

        if (is_numeric($value))
            return Taxonomia::where('grupo', 'vademecum')->where('tipo', $type)->where('id', $value)->first();

        return Taxonomia::getOrCreate('vademecum', $type, $value);
    }

    protected function prepareSearchedData($elementos)
    {
        $result = $elementos->toArray();

        $data = [];

        foreach ($result['data'] as $key => $row) {
            $data[$key]['id'] = $row['id'];
            $data[$key]['name'] = $row['name'];
            $data[$key]['scorm'] = $row['media']['file'] ?? null;
            $data[$key]['category'] = $row['category']['name'] ?? null;
            $data[$key]['category_id'] = $row['category_id'];
            $data[$key]['subcategory_id'] = $row['subcategory_id'];
        }

        $result['data'] = $data;

        return $result;
    }

    // todo: this method, is not used anywhere
    // protected function importFromFile($data)
    // {
    //     try {
    //         $model = (new VademecumImport);

    //         $model->modulo_id = $data['modulo_id'];
    //         $model->category_id = $data['category_id'];

    //         $model->import($data['excel']);

    //         if ($model->failures()->count()) {
    //             request()->session()->flash('excel-errors', $model->failures());

    //             return ['status' => 'error', 'message' => 'Se encontraron algunos errores.'];
    //         }
    //     } catch (\Exception $e) {
    //         // info($e);
    //         return ['status' => 'error', 'message' => $e->getMessage()];
    //     }

    //     return ['status' => 'success', 'message' => 'Registros ingresados correctamente.'];
    // }
}
