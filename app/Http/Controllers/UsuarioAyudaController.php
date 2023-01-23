<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsuarioAyudaResource;
use App\Models\Criterion;
use App\Models\Ticket;
use App\Models\Workspace;
use App\Models\UsuarioAyuda;
use App\Notifications\UsuarioAyudaNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioAyudaController extends Controller
{

    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $tickets = Ticket::search($request);

        UsuarioAyudaResource::collection($tickets);

        return $this->success($tickets);
    }

    /**
     * Get items list for select inputs
     *
     * @return JsonResponse
     */
    public function getListSelects()
    {
        $modulos = Workspace::loadSubWorkspaces(['id', 'name as nombre']);
        // $modulos = Criterion::getValuesForSelect('module');
        $estados = config('data.soporte-estados');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to show ticket details
     *
     * @param Request $request
     * @param Ticket $ticket
     * @return JsonResponse
     */
    public function show(Request $request, Ticket $ticket)
    {
        $request->merge(['view' => 'show']);

        $ticket = new UsuarioAyudaResource($ticket);

        return $this->success(compact('ticket'));
    }

    /**
     * Process request to load data for edit form
     *
     * @param Ticket $ticket
     * @return JsonResponse
     */
    public function edit(Ticket $ticket)
    {
        $estados = config('data.soporte-estados');
        $ticket->load('user');

        $key = array_search(
            $ticket->estado,
            array_column($estados, 'id')
        );

        $ticket->estado = $estados[$key];

        return $this->success(get_defined_vars());
    }

    /**
     *
     * @param Request $request
     * @param Ticket $ticket
     * @return JsonResponse
     */
    public function update(Request $request, Ticket $ticket)
    {
        if ($ticket->status == 'solucionado') {
            return $this->error('El ticket ya ha sido solucionado y no puede modificarse.');
        }

        $ticket->status = $request->all()['status'];
        $ticket->save();

        if ($ticket->status == 'solucionado' && $ticket->user)
        {
            // $user = $ticket->user;

            // $user->notify(new UsuarioAyudaNotification($ticket));
            // return $this->success(['msg' => 'Ticket solucionado y notificado correctamente.']);
        }

        return $this->success(['msg' => 'Ticket actualizado correctamente.']);
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
}
