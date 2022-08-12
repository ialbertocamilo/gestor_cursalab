<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\TipoCriterio;

use App\Http\Resources\CriterionValueResource;
use App\Http\Requests\CriterionValueStoreRequest;

use Illuminate\Http\Request;

class CriteriosController extends Controller
{
    public function search(Request $request)
    {
        $criterios = Criterio::search($request);

        CriterionValueResource::collection($criterios);

        return $this->success($criterios);
    }

    public function getListSelects()
    {
        $modulos = Abconfig::getModulesForSelect();

        return $this->success(get_defined_vars());
    }

    public function index()
    {
        return view('criterios.index');
    }

    public function getInitialData()
    {
        $criterios = Criterio::with('config')->withCount('usuarios')->paginate(15);
        $modulos = Abconfig::select('etapa','id')->get();
        return response()->json([
            'data' => $criterios->items(),
            'modulos' => $modulos,
            'pagina_actual' => $criterios->currentPage(),
            'total_paginas' => $criterios->lastPage(),
        ], 200);
    }

    public function create(TipoCriterio $tipo_criterio)
    {
        $modulos = Abconfig::getModulesForSelect();
        $tipos = TipoCriterio::getForSelect();

        return $this->success(get_defined_vars());
    }

    public function edit(TipoCriterio $tipo_criterio, Criterio $criterio)
    {
        $criterio->load('modulo');
        $criterio->tipo = $tipo_criterio;

        $modulos = Abconfig::getModulesForSelect();
        $tipos = TipoCriterio::getForSelect();

        return $this->success(get_defined_vars());
    }

    public function store(TipoCriterio $tipo_criterio, CriterionValueStoreRequest $request)
    {
        $data = $request->validated();

        $data['tipo_criterio'] = $tipo_criterio->nombre;
        $data['tipo_criterio_id'] = $tipo_criterio->id;

        $criterio = Criterio::create($data);

        $msg = 'Criterio creado correctamente.';

        return $this->success(compact('msg'));
    }

    public function update(TipoCriterio $tipo_criterio, Criterio $criterio, CriterionValueStoreRequest $request)
    {
        $data = $request->validated();

        $valor_changed = $criterio->valor != trim($data['valor']);

        $criterio->update($data);

        if ($valor_changed)
            Usuario::where('grupo', $criterio->id)->update(['grupo_nombre' => $criterio->valor]);

        $msg = 'Criterio actualizado correctamente.';

        return $this->success(compact('msg'));
    }

    // public function insert_or_edit(Request $request)
    // {
    //     $data = $request->all();
    //     $error = false;
    //     $mensaje = 'Este criterio ya existe';
    //     //Verificar si existe
    //     if($data['id']!=0){
    //         $existe = Criterio::where('id',trim($data['id']))->select('valor')->first();
    //         $error = ($existe) ?  false : true ;
    //         $mensaje = 'El criterio no se puede editar.';
    //         if(($existe) && ($existe->valor!=trim($data['valor']))){
    //             $s_nombre =  Criterio::where('config_id',$data['config_id'])->where('valor',trim($data['valor']))->select('id')->first();
    //             if($s_nombre){
    //                 $mensaje = 'Esta criterio ya existe dentro del módulo indicado.';
    //                 $error = true ;
    //             }
    //         }
    //     }else{
    //         $s_nombre =  Criterio::where('config_id',$data['config_id'])->where('valor',trim($data['valor']))->select('id')->first();
    //         if($s_nombre){
    //             $mensaje = 'Esta Criterio ya existe dentro del módulo indicado.';
    //             $error = true ;
    //         }
    //     }
    //     if(!$error){
    //         if($data['id']!=0){
    //             $user_bot = Usuario::where('grupo',$data['id'])->update([
    //                 'grupo_nombre' => $data['valor'],
    //             ]);
    //         }
    //         $botica = Criterio::updateOrCreate(
    //             ['id' => $data['id']],
    //             ['valor' => $data['valor'],
    //             'config_id' => $data['config_id'],
    //             'tipo_criterio' => $data['tipo_criterio'],
    //             'tipo_criterio_id'=>1]
    //         );
    //     }
    //     return response()->json(compact('error','mensaje'));
    // }

    public function buscar_criterio($botica_nombre){
        $criterios = Criterio::with(['config'=>function($q){
            $q->select('id','etapa');
        }],'usuarios')->where('valor','like','%'.$botica_nombre.'%')->withCount('usuarios')->paginate(15);
        return response()->json([
            'data' => $criterios->items(),
            'pagina_actual' => $criterios->currentPage(),
            'total_paginas' => $criterios->lastPage(),
        ], 200);
    }
}
