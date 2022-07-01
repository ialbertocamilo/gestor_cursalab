<?php

namespace App\Http\Controllers;

use App\Models\Botica;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;

use App\Http\Requests\BoticaStoreRequest;
use App\Http\Resources\BoticaResource;

use Illuminate\Http\Request;

class BoticaController extends Controller
{
    public function search(Request $request)
    {
        $boticas = Botica::searchForGrid($request);

        BoticaResource::collection($boticas);

        return $this->success($boticas);
    }

    public function getListSelects()
    {
        $modulos = Abconfig::getModulesForSelect();

        return $this->success(get_defined_vars());
    }

    public function create()
    {
        $modulos = Abconfig::getModulesForSelect();

        return $this->success(get_defined_vars());
    }

    public function store(BoticaStoreRequest $request)
    {
        $data = $request->validated();

        $botica = Botica::create($data);

        $msg = 'Sede creada correctamente.';

        return $this->success(compact('msg'));
    }

    // public function getFormSelects()
    // {
    //     $modulos = Abconfig::getModulesForSelect();

    //     return $this->success(get_defined_vars());
    // }

    public function edit(Botica $botica)
    {
        $modulos = Abconfig::getModulesForSelect();
        $grupos = Criterio::getForSelect('GRUPO', $botica->config_id);

        $botica->load('modulo', 'grupo');

        return $this->success(get_defined_vars());
    }

    public function update(Botica $botica, BoticaStoreRequest $request)
    {
        $data = $request->validated();

        $nombre_changed = $botica->nombre != trim($data['nombre']);

        $botica->update($data);

        if ($nombre_changed)
            Usuario::where('botica_id', $botica->id)->update(['botica' => $botica->nombre]);

        return $this->success(['msg' => 'Sede actualizada correctamente.']);
    }


    // public function index(){
    //     return view('boticas.index');
    // }

    // public function getInitialData()
    // {
    //     $boticas = Botica::with(['criterio' => function($q){
    //         $q->select('id','valor');
    //     },'config'=>function($q){
    //         $q->select('id','etapa');
    //     }])->withCount('usuarios')->paginate(15);
    //     $modulos = Abconfig::select('id','etapa')->get();
    //     return response()->json([
    //         'data' => $boticas->items(),
    //         'modulos' => $modulos,
    //         'pagina_actual' => $boticas->currentPage(),
    //         'total_paginas' => $boticas->lastPage(),
    //     ], 200);
    // }
    // public function insert_or_edit(Request $request){
    //     $data = $request->all();
    //     $error = false;
    //     $mensaje = 'Esta botica ya esxiste';
    //     //Verificar si existe
    //     if($data['id']!=0){
    //         $existe = Botica::where('id',trim($data['id']))->select('nombre')->first();
    //         $error = ($existe) ?  false : true ;
    //         $mensaje = 'La Botica no se puede editar.';
    //         if(($existe) && ($existe->nombre!=trim($data['nombre']))){
    //             $s_nombre =  Botica::where('config_id',$data['config_id'])->where('criterio_id',$data['criterio_id'])->where('nombre',trim($data['nombre']))->select('id')->first();
    //             if($s_nombre){
    //                 $mensaje = 'Esta Botica ya existe dentro del módulo y grupo indicado.';
    //                 $error = true ;
    //             }
    //         }
    //     }else{
    //         $s_nombre =  Botica::where('config_id',$data['config_id'])->where('criterio_id',$data['criterio_id'])->where('nombre',trim($data['nombre']))->select('id')->first();
    //         if($s_nombre){
    //             $mensaje = 'Esta Botica ya existe dentro del módulo y grupo indicado.';
    //             $error = true ;
    //         }
    //     }
    //     if(!$error){
    //         if($data['id']!=0){
    //             $user_bot = Usuario::where('botica_id',$data['id'])->update([
    //                 'botica' => $data['nombre'],
    //             ]);
    //         }
    //         $botica = Botica::updateOrCreate(
    //             ['id' => $data['id']],
    //             ['nombre' => $data['nombre'],
    //             'config_id' => $data['config_id'],
    //             'criterio_id' => $data['criterio_id'],
    //             'codigo_local'=>$data['codigo_local']]
    //         );
    //     }
    //     return response()->json(compact('error','mensaje'));
    // }

    public function delete_botica($id){
        $bot = Botica::where('id',$id)->delete();
        $user_bot = Usuario::where('botica_id',$id)->first();
        if($user_bot){
            return response()->json('error');
        }
        if($bot){
            return response()->json('deleted');
        }
        return response()->json('error');
    }

    public function destroy(Botica $botica)
    {
        $users_count = Usuario::where('botica_id', $botica->id)->count();

        if($users_count)
            return $this->error("Error al intentar eliminar. La sede se encuentra asociada a {$users_count} usuarios.");

        $botica->delete();

        return $this->success(['msg' => 'Sede eliminada correctamente.']);
    }
    // public function get_criterios($config_id){
    //     $criterios = Criterio::where('config_id',$config_id)->get();
    //     return response()->json([
    //         'criterios' => $criterios,
    //     ], 200);
    // }
    // public function buscar_botica($botica_nombre){
    //     $boticas = Botica::with(['criterio' => function($q){
    //         $q->select('id','valor');
    //     },'config'=>function($q){
    //         $q->select('id','etapa');
    //     }])->withCount('usuarios')->where('nombre','like','%'.$botica_nombre.'%')->paginate(15);
    //     return response()->json([
    //         'data' => $boticas->items(),
    //         'pagina_actual' => $boticas->currentPage(),
    //         'total_paginas' => $boticas->lastPage(),
    //     ], 200);
    // }

    public function getGroupsByModule(Request $request)
    {
        $grupos = Criterio::getForSelect('GRUPO', $request->config_id);

        return $this->success(get_defined_vars());
    }

    public function searchNoPaginate(Request $request)
    {
        $boticas = Botica::prepareSearchQuery($request)->get();

        return $this->success(compact('boticas'));
    }

}
