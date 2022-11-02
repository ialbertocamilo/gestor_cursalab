<?php

namespace App\Models;

use App\Imports\GlosarioImport;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


class Glossary extends Model
{

    use SoftDeletes;
    use ApiResponse;

    protected $fillable = [
        'name', 'categoria_id', 'jerarquia_id', 'laboratorio_id',
        'condicion_de_venta_id', 'via_de_administracion_id',
        'grupo_farmacologico_id', 'forma_farmaceutica_id', 'dosis_adulto_id',
        'dosis_nino_id', 'recomendacion_de_administracion_id',
        'contraindicacion_id', 'interacciones_frecuentes_id',
        'reacciones_frecuentes_id', 'advertencias_id', 'active'
    ];

    protected $hidden = [
        'laboratorio_id', 'condicion_de_venta_id',
        'via_de_administracion_id', 'grupo_farmacologico_id',
        'forma_farmaceutica_id', 'dosis_adulto_id', 'dosis_nino_id',
        'recomendacion_de_administracion_id', 'contraindicacion_id',
        'interacciones_frecuentes_id', 'reacciones_frecuentes_id',
        'advertencias_id', 'created_at', 'updated_at', 'deleted_at'
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

    public function modules()
    {
        return $this->belongsToMany(
            CriterionValue::class,
            'glossary_module',
            'glossary_id',
            'module_id'
        );
    }

    public function glossary_module() {

        return $this->hasMany(GlossaryModule::class);
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

    /**
     * Insert data from Excel file into database
     *
     * @param $data
     * @return JsonResponse
     */
    protected function importFromFile($data)
    {
        try {

            $model = new GlosarioImport;
            $model->modulo_id = $data['modulo_id'];
            $model->categoria_id = $data['categoria_id'];
            $model->import($data['file_excel']);

            if ($model->failures()->count()) {
                // request()->session()->flash('excel-errors', $model->failures());
                return $this->errors(
                    $model->failures(),
                    'Se encontraron algunos errores.', 422
                );
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
        $relationships = ['modules', 'categoria'];

        if ($api) {

            $relationships = [
                'modules' => function ($q) use ($request) {
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

        if ($request->q) {
            $query->where('name', 'like', "%{$request->q}%");
        }

        if ($request->modulo_id) {

            $query->join('glossary_module', 'glossary_module.glossary_id', '=', 'glossaries.id')
                  ->where('glossary_module.module_id', $request->modulo_id);
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

            // Save modules

            $modules = [];
            if (isset($data['modulos'])) {
                foreach ($data['modulos'] as $module) {
                    if (isset($module['code'])) {
                        $modules[] = [
                            'glossary_id' => $glossary->id,
                            'module_id' => $module['id'],
                            'code' => $module['code']
                        ];
                    }
                }
            }
            $glossary->glossary_module()->delete();
            GlossaryModule::insert($modules);

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

    protected function getCareersCategory($modulos, $code = 'position_code') {
        $carreras_module = Carrera::with('glosario_categorias')->get();

        $criterios = CriterionValue::query() 
                                   ->whereRelation('criterion', 'code', $code)
                                   ->where('active', ACTIVE)
                                   ->select('id', "value_text as nombre")
                                   ->get();
                                   //->limit(5)->get();
        
        $stack_categories = [];
        foreach ($carreras_module as $cm_module) {
            if(!$cm_module->glosario_categoria_id) {
                $stack_categories[$cm_module->module_id][$cm_module->carrera_id] = []; 
            }else {
                $stack_categories[$cm_module->module_id][$cm_module->carrera_id][]['id'] = $cm_module->glosario_categoria_id; 
            }
        }

        $carreras = [];
        foreach($modulos as $modulo) {

            foreach ($criterios as $key => $criterio) {
                $categories = $stack_categories[$modulo->id][$criterio->id] ?? [];
                
                $stack[$key]['id'] = $criterio->id;
                $stack[$key]['nombre'] = $criterio->nombre;
                $stack[$key]['glosario_categorias'] = $categories;
            }
            $carreras[$modulo->id] = $stack;
        }

        return $carreras;
    }


    protected function insertCareerCategory($module_id, $carrera_id, $data) 
    {
        foreach ($data as $key => ['id' => $index]) {

            $instance = new Carrera;
            
            $instance->module_id = $module_id;
            $instance->carrera_id = $carrera_id;
            $instance->glosario_categoria_id = $index;

            $instance->save();
        }
    }

    protected function deleteCareerCategory($module_id, $carrera_id) 
    {
        return Carrera::where('module_id', $module_id)
                      ->where('carrera_id', $carrera_id)
                      ->delete();
    }

    protected function checkRowIsAvailable($module_id, $carrera_id)
    {
        return Carrera::where('module_id', $module_id)
                      ->where('carrera_id', $carrera_id)
                      ->count();
    }

    protected function setNullCategories($module_id, $carrera_id) {

        return Carrera::where('module_id', $module_id)
                      ->where('carrera_id', $carrera_id)
                      ->update(['glosario_categoria_id' => NULL]);
    } 

    protected function storeCarreerCategories($data)
    {
        try {

            $message = 'Registros actualizados correctamente';
            DB::beginTransaction();

            ['modulos_carreras' => $modulos_carreras] = $data;

            foreach($modulos_carreras as $module_id => $carreras) {
                // $module_id = module_id
                foreach ($carreras as $key => [ 'id' => $carrera_id,
                                                'glosario_categorias' => $glosario_categorias]) 
                {
                    $check_categories = empty($glosario_categorias); 
                    $check_available = $this->checkRowIsAvailable($module_id, $carrera_id);

                    #update insert dinamic
                    if($check_available) {

                        if($check_categories) {
                            $this->deleteCareerCategory($module_id, $carrera_id);
                        }

                        if(!$check_categories) {
                            $this->deleteCareerCategory($module_id, $carrera_id);
                            $this->insertCareerCategory($module_id, $carrera_id, $glosario_categorias);
                        }

                    } else {
                        $this->insertCareerCategory($module_id, $carrera_id, $glosario_categorias);
                    }
            
                }
            }

            /*foreach ($data['modulos_carreras'] as $modulo_id => $carreras)
            {
                foreach($carreras as $carrera)
                {
                    $model = Carrera::find($carrera['id']);

                    $categorias = !empty($carrera['glosario_categorias']) ? array_column($carrera['glosario_categorias'], 'id') : [];
                    // $categorias = !empty($row['glosario_categoria_id']) ? $row['glosario_categoria_id'] : [];

                    $model->glosario_categorias()->sync($categorias);

                }
            }*/

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

        if (!empty($data[$key])) {

            foreach ($data[$key] as $i => $value) {

                $taxonomia = $this->getOrCreateTaxonomyByValue($type, $value);

                if ($taxonomia) {
                    $ids[$taxonomia->id] = [
                        'glossary_group_id' => Glossary::GRUPOS[$type]
                    ];
                }
            }
        }

        return $ids;
    }

    public function prepareModulosData($data)
    {
        foreach ($data as $key => $row)
        {
            unset($data[$key]['id']);
            unset($data[$key]['name']);

            if (empty($row['code']))
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
            $modulo = $row->modules->first();

            $data[$key]['name'] = $row->name;
            # code glosario
            # $data[$key]['codigo'] = $modulo->pivot->codigo ?? '';
            $data[$key]['codigo'] = $row->code ?? '';

            $data[$key]['categoria'] = $row->categoria->name ?? '';
            $data[$key]['jerarquia'] = $row->jerarquia->name ?? '';
            $data[$key]['laboratorio'] = $row->laboratorio->name ?? '';
            $data[$key]['advertencia'] = $row->advertencias->name ?? '';
            $data[$key]['condicion_de_venta'] = $row->condicion_de_venta->name ?? '';
            $data[$key]['via_de_administracion'] = $row->via_de_administracion->name ?? '';
            $data[$key]['grupo_farmacologico'] = $row->grupo_farmacologico->name ?? '';
            $data[$key]['dosis_adulto'] = $row->dosis_adulto->name ?? '';
            $data[$key]['dosis_nino'] = $row->dosis_nino->name ?? '';
            $data[$key]['recomendacion_de_administracion'] = $row->recomendacion_de_administracion->name ?? '';

            $data[$key]['principios_activos'] = $row->principios_activos->pluck('name')->toArray();
            $data[$key]['contraindicaciones'] = $row->contraindicaciones->pluck('name')->toArray();
            $data[$key]['reacciones'] = $row->reacciones->pluck('name')->toArray();
            $data[$key]['interacciones'] = $row->interacciones->pluck('name')->toArray();
        }

        $result['data'] = $data;

        return $result;
    }

    protected function getModulesWithCode($glossaryModules)
    {

        $modules = Criterion::getValuesForSelect('module');

        $glossaryModules = $glossaryModules->toArray();
        foreach ($glossaryModules as $module) {

            $_module = $modules->find($module['module_id']);
            $_module->code = $module['code'] ?? '';
        }



        return $modules;
    }
}
