<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Imports\VademecumImport;
use DB;


class Vademecum extends Model
{
    use SoftDeletes;
    


    protected $table = 'vademecum';

    protected $fillable = [
        'nombre', 'categoria_id', 'subcategoria_id', 'media_id', 'estado'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente',
        ];
    }
    /* AUDIT TAGS */

    // Relationships

    public function modulos()
    {
        return $this->belongsToMany(Abconfig::class, 'vademecum_modulo', 'vademecum_id', 'modulo_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Taxonomia::class, 'categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(Taxonomia::class, 'subcategoria_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function actions()
    {
        return $this->morphMany(UsuarioAccion::class, 'model');
    }

    public function incrementAction($type_id, $quantity = 1)
    {
        $user_id = auth()->user()->id;

        $row = $this->actions()
            ->firstOrCreate(compact('user_id', 'type_id'));

        $row->increment('score', $quantity);

        return $row;
    }

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    // Functions

    // protected function importFromFile($data)
    // {
    //     try {
    //         $model = (new VademecumImport);

    //         $model->modulo_id = $data['modulo_id'];
    //         $model->categoria_id = $data['categoria_id'];

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

    protected function search($request, $api = false, $paginate = 20)
    {
        $relationships = ['modulos', 'categoria', 'subcategoria', 'media'];

        if ($api) :

            $relationships = [
                'modulos' => function ($q) use ($request) {
                    if ($request->modulo_id)
                        $q->where('id', $request->modulo_id);
                },
                'subcategoria',
                'categoria',
                'media',
            ];

        endif;

        $query = Vademecum::with($relationships);

        if ($request->categoria_id)
            $query->where('categoria_id', $request->categoria_id);

        if ($request->subcategoria_id)
//            $query->where('subcategoria_id', $request->subcategoria_id);
            $query->whereIn('subcategoria_id', explode(',', $request->subcategoria_id));


        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        if ($request->modulo_id)
            $query->whereHas('modulos', function ($q) use ($request) {
                $q->where('id', $request->modulo_id);
            });

        if ($request->estado)
            $query->where('estado', $request->estado);

        // if ($api)
        //     $query->orderBy('nombre', 'ASC');

        if ($api){
            $query->orderBy('nombre', 'ASC');
        }else{
            $field = $request->sortBy ?? 'nombre';
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
            $query->where('nombre', 'like', "%{$request->q}%");

        $categorias = $query->paginate($paginate);

        return $categorias;
    }

    protected function storeRequest($data, $elemento = null)
    {
        try {

            DB::beginTransaction();

            // $taxonomia = $this->prepareTaxonomy($data, 'categoria_id', 'categoria');
            // $data['categoria_id'] = $taxonomia->id ?? NULL;


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
            $data[$key]['nombre'] = $row['nombre'];
            $data[$key]['scorm'] = $row['media']['file'] ?? null;
            $data[$key]['categoria'] = $row['categoria']['nombre'] ?? null;
            $data[$key]['categoria_id'] = $row['categoria_id'];
            $data[$key]['subcategoria_id'] = $row['subcategoria_id'];
        }

        $result['data'] = $data;

        return $result;
    }
}
