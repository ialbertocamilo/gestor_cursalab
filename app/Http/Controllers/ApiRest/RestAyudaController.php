<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Taxonomy;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RestAyudaController extends Controller
{
    public function registra_ayuda(Request $request)
    {
        $usuario_id = strip_tags($request->input('usuario_id'));
        $motivo = strip_tags($request->input('motivo'));
        $detalle = strip_tags($request->input('detalle'));
        $contacto = strip_tags($request->input('contacto'));
        $user = Auth::user();
        if (is_null($usuario_id) || is_null($motivo)) {
            $data = array('error' => true, 'error_msg' => 'No se recibieron datos', 'data' => null);
        } else {
            $id = Ticket::insertGetId(array(
                'user_id' => $usuario_id,
                'reason' => $motivo,
                'detail' => $detalle,
                'contact' => $contacto,
            ));
            // $modulo = Abconfig::where('id', $user->config_id)->select('etapa')->first();
            // $mensaje = '*_Nueva incidencia:_* \n Empresa: Universidad Corporativa \n Módulo: ' . $modulo->etapa . ' \n DNI : ' . $user->dni . ' \n Ticket: #' . $id . ' \n Motivo : ' . $motivo . ' \n Enlace: ' . env('URL_GESTOR') . 'usuario_ayuda/index?id=' . $id;
            // UsuarioAyuda::send_message_to_slack($mensaje);
            $data = array('error' => false, 'data' => ['ticket' => $id]);
        }
        return $data;
    }

    public function preguntas_seccion_ayuda()
    {
        $tax_id = Taxonomy::where('type', 'section')->where('code', 'ayuda_app')->first('id');

        $preguntas = Post::where('section_id', $tax_id->id)->get();

        return response()->json(compact('preguntas'));
    }

    public function preguntas_frecuentes()
    {
        $tax_id = Taxonomy::where('type', 'section')->where('code', 'faq')->first('id');

        $preguntas = Post::where('section_id', $tax_id->id)->get();

        return response()->json(compact('preguntas'));
    }
}
