<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\UsuarioAyuda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\UsuarioAyudaNotification;

use App\Http\Resources\UsuarioAyudaResource;

class UsuarioAyudaController extends Controller
{
    // public function index(){
    //     return view('usuario_ayuda.index');
    // }

    public function search(Request $request)
    {
        $ayudas = UsuarioAyuda::search($request);

        UsuarioAyudaResource::collection($ayudas);

        return $this->success($ayudas);
    }

    public function getListSelects()
    {
        $modulos = Abconfig::getModulesForSelect();
        $estados = config('data.soporte-estados');

        return $this->success(get_defined_vars());
    }

    public function getData(Request $request){
        // $data = $request->all();
        // $searchString = $data['in_search'];
        // $query = UsuarioAyuda::with(['usuario'=>function($q){
        //     $q->select('id','nombre','dni','config_id');
        // },'usuario.config'=>function($q){
        //     $q->select('id','etapa');
        // }]);
        //Filtro de fechas
        // if(isset($data['start']) || isset($data['end'])){
        //     $start = (isset($data['start'])) ? ($data['start'].' 00:00:00') : '20001-09-14 00:00:00' ;
        //     $end = (isset($data['end'])) ?($data['end'].' 23:59:59') : date("Y-m-d 23:59:59") ;
        //     $query = $query->whereBetween('created_at',[$start,$end]);
        // }
        //Filtro de estado
        // if($data['estado'] != 'todos' ){
        //     $query = $query->where('estado',$data['estado']);
        // }
        // if(!empty($searchString)){
        //     if (str_contains($searchString, '#')) {
        //         $searchString = substr($searchString, 1);
        //     }
        //     $query = $query->WhereHas('usuario', function($q) use ($searchString) {
        //             $q->where('dni', 'like', '%'.$searchString.'%')
        //             ->orWhere('nombre', 'like', '%'.$searchString.'%')
        //             ->orWhereHas('config', function($q) use ($searchString) {
        //                 $q->where('etapa', 'like', '%'.$searchString.'%')->select('id','etapa');
        //             })
        //             ->select('id','nombre','dni','config_id');
        //     })->orWhere('id','like','%'.$searchString.'%');
        // }
        // $mensajes = $query->orderBy('created_at','desc')
        //             ->select('*',DB::raw('CONCAT("#",id) as ticket'))
        //             ->paginate($data['items_per_page']);
        // return response()->json(compact('mensajes'));
    }

    public function changeEstado(Request $request){
        // $data = $request->all();
        // $usuario_ayuda = UsuarioAyuda::find($data['id']);
        // $usuario_ayuda->estado = $data['estado'];
        // $usuario_ayuda->info_soporte = $data['info_soporte'];
        // $usuario_ayuda->msg_to_user = $data['msg_to_user'];
        // $usuario_ayuda->update();
        // if($usuario_ayuda->estado=='solucionado'){
        //     $user = Usuario::find($usuario_ayuda->usuario_id);
        //     if($user){
        //         $user->notify(new UsuarioAyudaNotification($usuario_ayuda));
        //     }
        // }
        // return response()->json(compact('data'));
    }

    public function edit(UsuarioAyuda $usuario_ayuda)
    {
        $estados = config('data.soporte-estados');

        $key = array_search($usuario_ayuda->estado, array_column($estados, 'id'));

        $usuario_ayuda->estado = $estados[$key]; 

        return $this->success(get_defined_vars());
    }

    public function show(Request $request, UsuarioAyuda $usuario_ayuda)
    {
        $request->merge(['view' => 'show']);

        $usuario_ayuda = new UsuarioAyudaResource($usuario_ayuda);

        return $this->success(compact('usuario_ayuda'));
    }

    public function update(Request $request, UsuarioAyuda $usuario_ayuda)
    {
        if($usuario_ayuda->estado == 'solucionado')
            return $this->error('El ticket ya ha sido solucionado y no puede modificarse.');

        $usuario_ayuda->update($request->all());

        if($usuario_ayuda->estado == 'solucionado')
        {
            $user = $usuario_ayuda->usuario;

            if($user)
            {
                $user->notify(new UsuarioAyudaNotification($usuario_ayuda));
                return $this->success(['msg' => 'Ticket solucionado y notificado correctamente.']);
            }
        }
        
        return $this->success(['msg' => 'Ticket actualizado correctamente.']);
    }
}
