<?php

namespace App\Models;

use App\Imports\GlosarioImport;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Glossary extends Model
{

    use SoftDeletes;
    use ApiResponse;

    protected $fillable = [
        'name', 'categoria_id', 'jerarquia_id', 'laboratorio_id', 'condicion_de_venta_id', 'via_de_administracion_id',
        'grupo_farmacologico_id', 'forma_farmaceutica_id', 'dosis_adulto_id', 'dosis_nino_id',
        'recomendacion_de_administracion_id', 'contraindicacion_id', 'interacciones_frecuentes_id',
        'reacciones_frecuentes_id', 'advertencias_id', 'active'
    ];

    protected $hidden = [
        'categoria_id', 'laboratorio_id', 'condicion_de_venta_id', 'via_de_administracion_id',
        'grupo_farmacologico_id', 'forma_farmaceutica_id', 'dosis_adulto_id', 'dosis_nino_id',
        'recomendacion_de_administracion_id', 'contraindicacion_id', 'interacciones_frecuentes_id',
        'reacciones_frecuentes_id', 'advertencias_id', 'created_at', 'updated_at', 'deleted_at'
    ];

    const GRUPOS = [
        'principio_activo' => 4,
        'contraindicacion' => 5,
        'interaccion' => 6,
        'reaccion' => 7,
    ];

    /*

        Relationships

    --------------------------------------------------------------------------*/


    public function principios_activos()
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'glossary_taxonomy',
            'glossary_id',
            'taxonomy_id'
        )->wherePivot(
            'glossary_group_id',
            Glossary::GRUPOS['principio_activo']
        );
    }

    public function contraindicaciones()
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'glossary_taxonomy',
            'glossary_id',
            'taxonomy_id'
        )->wherePivot(
            'glossary_group_id',
            Glossary::GRUPOS['contraindicacion']
        );
    }

    public function interacciones()
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'glossary_taxonomy',
            'glossary_id',
            'taxonomy_id'
        )->wherePivot(
            'glossary_group_id',
            Glossary::GRUPOS['interaccion']
        );
    }

    public function reacciones()
    {
        return $this->belongsToMany(
            Taxonomy::class,
            'glossary_taxonomy',
            'glossary_id',
            'taxonomy_id'
        )->wherePivot(
            'glossary_group_id',
            Glossary::GRUPOS['reaccion']
        );
    }

    public function modulos()
    {
        return $this->belongsToMany(
            GlossaryModule::class,
            'glossary_module',
            'glossary_id',
            'module_id'
        )->withPivot('code');
    }

    public function laboratorio()
    {
        return $this->belongsTo(
            Taxonomy::class, 'laboratorio_id'
        );
    }

    public function categoria()
    {
        return $this->belongsTo(
            Taxonomy::class, 'categoria_id'
        );
    }

    public function jerarquia()
    {
        return $this->belongsTo(
            Taxonomy::class, 'jerarquia_id'
        );
    }

    public function advertencias()
    {
        return $this->belongsTo(
            Taxonomy::class, 'advertencias_id'
        );
    }

    public function condicion_de_venta()
    {
        return $this->belongsTo(
            Taxonomy::class, 'condicion_de_venta_id'
        );
    }

    public function via_de_administracion()
    {
        return $this->belongsTo(
            Taxonomy::class, 'via_de_administracion_id'
        );
    }

    public function grupo_farmacologico()
    {
        return $this->belongsTo(
            Taxonomy::class, 'grupo_farmacologico_id'
        );
    }

    public function dosis_adulto()
    {
        return $this->belongsTo(
            Taxonomy::class, 'dosis_adulto_id'
        );
    }

    public function dosis_nino()
    {
        return $this->belongsTo(
            Taxonomy::class, 'dosis_nino_id'
        );
    }

    public function recomendacion_de_administracion()
    {
        return $this->belongsTo(
            Taxonomy::class,
            'recomendacion_de_administracion_id'
        );
    }


    public function setEstadoAttribute($value)
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

    protected function importFromFile($data)
    {
        try {
            $model = (new GlosarioImport);

            $model->modulo_id = $data['modulo_id'];
            $model->categoria_id = $data['categoria_id'];

            $model->import($data['file_excel']);

            if ($model->failures()->count()) {
                // request()->session()->flash('excel-errors', $model->failures());
                return $this->errors($model->failures(), 'Se encontraron algunos errores.', 422);

            }

        } catch (Exception $e) {

            DB::rollBack();

            return $this->error($e->getMessage(), 422);
        }

        return $this->success(['msg' => 'Registros ingresados correctamente.']);
    }

    /**
     * Search records from database
     *
     * @param $request
     * @param bool $api
     * @param int $paginate
     * @return LengthAwarePaginator
     */
    protected function search($request, bool $api = false, int $paginate = 20)
    {
        $relationships = ['modulos', 'categoria'];

        if ($api) {

            $relationships = [
                'modulos' => function ($q) use ($request) {
                    if ($request->module_id)
                        $q->where('id', $request->module_id);
                },
                'categoria', 'principios_activos', 'contraindicaciones',
                'interacciones', 'reacciones', 'laboratorio', 'advertencias',
                'condicion_de_venta', 'via_de_administracion', 'jerarquia',
                'grupo_farmacologico', 'dosis_adulto', 'dosis_nino',
                'recomendacion_de_administracion'
            ];

        }

        $query = Glossary::with($relationships);

        if ($request->q || $request->modulo_id) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->q}%")
                    ->orWhereHas('modulos', function ($q) use ($request) {
                        if ($request->q)
                            $q->where('glosario_modulo.codigo', $request->q);

                        if ($request->modulo_id)
                            $q->where('id', $request->modulo_id);
                    });
            });
        }

        if ($request->modulo_id) {
            $query->whereHas('modulos', function ($q) use ($request) {
                $q->where('id', $request->modulo_id);
            });
        }

        if ($request->grupo_farmacologico_id)
            $query->where('grupo_farmacologico_id', $request->grupo_farmacologico_id);

        if ($request->categoria_id)
            $query->where('categoria_id', $request->categoria_id);

        if ($request->laboratorio_id)
            $query->where('laboratorio_id', $request->laboratorio_id);

        if ($request->active)
            $query->where('active', $request->active);

        if ($request->principios_activos)
            $query->whereHas('principios_activos', function ($q) use ($request) {
                $q->whereIn('id', $request->principios_activos);
            });

        if ($api) {

            $query->orderBy('name', 'ASC');

        } else {

            $field = $request->sortBy ?? 'name';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
            $query->orderBy($field, $sort);
        }

        return $query->paginate($paginate);
    }

    /**
     * Save record in database
     *
     * @param $data
     * @param $glossary
     * @return array|string[]
     */
    protected function storeRequest($data, $glossary = null)
    {
        try {

            $data = $this->prepareData($data);

            DB::beginTransaction();

            if ($glossary) {

                $glossary->update($data);
                $message = 'Registro actualizado correctamente';

            } else {

                $glossary = $this->create($data);
                $message = 'Registro creado correctamente';

            }

            $modulos = $this->prepareModulosData($data['modulos']);
            $glossary->modulos()->sync($modulos);

            $taxonomias = $this->prepareTaxonomiesData(
                $data, 'principios_activos', 'principio_activo'
            );
            $glossary->principios_activos()->sync($taxonomias);

            $taxonomias = $this->prepareTaxonomiesData(
                $data, 'interacciones', 'interaccion'
            );
            $glossary->interacciones()->sync($taxonomias);

            $taxonomias = $this->prepareTaxonomiesData(
                $data, 'contraindicaciones', 'contraindicacion'
            );
            $glossary->contraindicaciones()->sync($taxonomias);

            $taxonomias = $this->prepareTaxonomiesData(
                $data, 'reacciones', 'reaccion'
            );
            $glossary->reacciones()->sync($taxonomias);

            DB::commit();

        } catch (Exception $e) {

            report($e);
            DB::rollBack();

            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        return ['status' => 'success', 'message' => $message];
    }

    protected function storeCarreerCategories($data)
    {
        try {

            $message = 'Registros actualizados correctamente';

            DB::beginTransaction();

            foreach ($data['modulos_carreras'] as $modulo_id => $carreras)
            {
                foreach($carreras AS $carrera)
                {
                    $model = Carrera::find($carrera['id']);

                    $categorias = !empty($carrera['glosario_categorias']) ? array_column($carrera['glosario_categorias'], 'id') : [];
                    // $categorias = !empty($row['glosario_categoria_id']) ? $row['glosario_categoria_id'] : [];

                    $model->glosario_categorias()->sync($categorias);
                }
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            return $this->error($e->getMessage(), 422);
        }

        return $this->success(['msg' => $message]);
    }

    public function prepareData($data)
    {
        $selects = config('data.glosario.selects');

        foreach ($selects as $key => $select) :

            if ($select['multiple'] == false) :

                $taxonomia = $this->prepareTaxonomy($data, $select['relation'], $select['key']);
                $data[$select['id']] = NULL;

                if ($taxonomia) :
                    $data[$select['id']] = $taxonomia->id;
                endif;

                unset($data[$select['relation']]);
            endif;

        endforeach;

        return $data;
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


        if (is_numeric($value)) {

            return Taxonomy::where('group', 'glosario')
                ->where('type', $type)
                ->where('id', $value)
                ->first();
        }

        return Taxonomy::getOrCreate('glosario', $type, $value);
    }

    public function prepareTaxonomiesData($data, $key, $type)
    {
        $ids = [];

        if (!empty($data[$key])) :

            foreach ($data[$key] as $i => $value) :

                $taxonomia = $this->getOrCreateTaxonomyByValue($type, $value);

                if ($taxonomia) :
                    $ids[$taxonomia->id] = ['glossary_group_id' => Glossary::GRUPOS[$type]];
                endif;

            endforeach;

        endif;

        return $ids;
    }

    public function prepareModulosData($data)
    {
        foreach ($data as $key => $row)
        {
            unset($data[$key]['id']);
            unset($data[$key]['name']);

            if (empty($row['codigo']))
                unset($data[$key]);
        }


        return $data;
    }

    protected function prepareSearchedData($glossaries)
    {
        $result['current_page'] = $glossaries->currentPage();
        $result['first_page_url'] = $glossaries->url(1);
        $result['from'] = $glossaries->firstItem();
        $result['last_page'] = $glossaries->lastPage();
        $result['last_page_url'] = $glossaries->url($glossaries->lastPage());
        $result['next_page_url'] = $glossaries->nextPageUrl();
        $result['path'] = $glossaries->getOptions()['path'];
        $result['per_page'] = $glossaries->perPage();
        $result['prev_page_url'] = $glossaries->previousPageUrl();
        $result['to'] = $glossaries->lastItem();
        $result['total'] = $glossaries->total();

        $data = [];

        foreach ($glossaries as $key => $row)
        {
            $modulo = $row->modulos->first();

            $data[$key]['name'] = $row->name;
            $data[$key]['codigo'] = $modulo->pivot->codigo ?? '';

            $data[$key]['categoria'] = $row->categoria->nombre ?? '';
            $data[$key]['jerarquia'] = $row->jerarquia->nombre ?? '';
            $data[$key]['laboratorio'] = $row->laboratorio->nombre ?? '';
            $data[$key]['advertencia'] = $row->advertencias->nombre ?? '';
            $data[$key]['condicion_de_venta'] = $row->condicion_de_venta->nombre ?? '';
            $data[$key]['via_de_administracion'] = $row->via_de_administracion->nombre ?? '';
            $data[$key]['grupo_farmacologico'] = $row->grupo_farmacologico->nombre ?? '';
            $data[$key]['dosis_adulto'] = $row->dosis_adulto->nombre ?? '';
            $data[$key]['dosis_nino'] = $row->dosis_nino->nombre ?? '';
            $data[$key]['recomendacion_de_administracion'] = $row->recomendacion_de_administracion->nombre ?? '';

            $data[$key]['principios_activos'] = $row->principios_activos->pluck('nombre')->toArray();
            $data[$key]['contraindicaciones'] = $row->contraindicaciones->pluck('nombre')->toArray();
            $data[$key]['reacciones'] = $row->reacciones->pluck('nombre')->toArray();
            $data[$key]['interacciones'] = $row->interacciones->pluck('nombre')->toArray();
        }

        $result['data'] = $data;

        return $result;
    }

    protected function getModulesWithCode($modulos = NULL)
    {
        $modules = Abconfig::getModulesForSelect();

        foreach($modules AS $key => $module)
        {
            $modulo = $modulos ? $modulos->where('id', $module->id)->first() : NULL;

            $modules[$key]['codigo'] = $modulo->pivot->codigo ?? '';
            $modules[$key]['modulo_id'] = $module->id;
        }

        return $modules;
    }
}
