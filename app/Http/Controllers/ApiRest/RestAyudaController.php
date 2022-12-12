<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoporteLoginRequest;
use App\Mail\SendEmailSupportLogin;
use App\Models\AssignedRole;
use App\Models\Post;
use App\Models\Role;
use App\Models\Taxonomy;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            if(strlen($motivo) == 1){
                $pregunta = Post::select('title')->where('id',$motivo)->first();
                $motivo = $pregunta->title ?? '';
            }
            $id = Ticket::insertGetId(array(
                'user_id' => $user->id,
                'reason' => $motivo,
                'detail' => $detalle,
                'contact' => $contacto,
                'workspace_id' => $user->subworkspace?->parent_id,
                'dni'=>$user->document,
                'name'=>$user->name,
                'status' => 'pendiente'
            ));
            // $modulo = Abconfig::where('id', $user->config_id)->select('etapa')->first();
            // $mensaje = '*_Nueva incidencia:_* \n Empresa: Universidad Corporativa \n Módulo: ' . $modulo->etapa . ' \n DNI : ' . $user->dni . ' \n Ticket: #' . $id . ' \n Motivo : ' . $motivo . ' \n Enlace: ' . env('URL_GESTOR') . 'usuario_ayuda/index?id=' . $id;
            // UsuarioAyuda::send_message_to_slack($mensaje);
            $data = array('error' => false, 'data' => ['ticket' => $id]);
        }
        return $data;
    }
    public function registra_ayuda_login(SoporteLoginRequest $request)
    {
        $workspace_id = strip_tags($request->input('workspace_id'));
        $workspace_name = strip_tags($request->input('workspace_name'));
        $name = strip_tags($request->input('name'));
        $dni = strip_tags($request->input('dni'));
        $phone = strip_tags($request->input('phone'));
        $details = strip_tags($request->input('details'));

        $data = array(
            'dni' => $dni,
            'contact' => $phone,
            'detail' => $details,
            'workspace_id' => $workspace_id,
            'name' => $name,
            'reason' => 'Soporte Login',
            'status' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now()
        );

        if (is_null($name) || is_null($workspace_id) || is_null($dni) || is_null($phone)) {
            $response = array('error' => true, 'error_msg' => 'No se recibieron datos', 'data' => null);
        } else {
            $rol = Role::where('name', 'admin')->first();
            $admins = AssignedRole::where('role_id', $rol->id)->where('scope', $workspace_id)->where('entity_type', User::class)->get('entity_id');
            $users = array();
            if (!is_null($admins)) {
                foreach ($admins as $adm) {
                    array_push($users, $adm->entity_id . "");
                }
            }
            $send_users = User::whereIn('id', $users)->get('email');
            $emails = array();
            if (!is_null($send_users)) {
                foreach ($send_users as $adm) {
                    array_push($emails, $adm->email);
                }
            }

            $id = Ticket::insertGetId($data);
            $response = array('error' => false, 'data' => ['ticket' => $id]);
            // $data_email = array(
            //     'nombre' => $name,
            //     'empresa' => $workspace_name,
            //     'dni' => $dni,
            //     'telefono' => $phone,
            //     'detalle' => $details
            // );
            // $emails = ['kevin@cursalab.io', 'rodrigo@cursalab.io'];
            // foreach ($emails as $email_to) {
            //     Mail::to($email_to)->send(new SendEmailSupportLogin($data_email));
            // }
        }
        return response()->json(compact('response'));
    }

    public function listar_empresas()
    {
        $workspaces = Workspace::whereNull('parent_id')->get(['id', 'name', 'slug']);

        return response()->json(compact('workspaces'));
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
