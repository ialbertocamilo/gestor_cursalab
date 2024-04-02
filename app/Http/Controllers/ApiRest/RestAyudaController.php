<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UsuarioController;
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
            if(strlen($motivo) < 3){
                $pregunta = Post::select('title')->where('id',$motivo)->first();
                $motivo = $pregunta->title ?? '';
            }

            $ticket = Ticket::create(array(
                'user_id' => $user->id,
                'reason' => $motivo,
                'detail' => $detalle,
                'contact' => $contacto,
                'workspace_id' => $user->subworkspace?->parent_id,
                'dni'=>$user->document,
                'name'=>$user->fullname,
                'status' => 'pendiente'
            ));
            $id = $ticket->id;

            // $modulo = Abconfig::where('id', $user->config_id)->select('etapa')->first();
            // $mensaje = '*_Nueva incidencia:_* \n Empresa: Universidad Corporativa \n Módulo: ' . $modulo->etapa . ' \n DNI : ' . $user->dni . ' \n Ticket: #' . $id . ' \n Motivo : ' . $motivo . ' \n Enlace: ' . env('URL_GESTOR') . 'usuario_ayuda/index?id=' . $id;
            // UsuarioAyuda::send_message_to_slack($mensaje);
            $data = array('error' => false, 'data' => ['ticket' => $id]);
        }
        return $data;
    }
    public function registra_ayuda_login(SoporteLoginRequest $request)
    {
        $dni = strip_tags($request->dni);
        $email = strip_tags($request->email);
        $phone = strip_tags($request->phone);
        $details = strip_tags($request->details);
        $name = null;
        $workspace_id = null;

        // Set data to store

        $user = User::where('document', $dni)->first();

        if ($user) {

            $data = [
                'dni' => $dni,
                'email' => $email,
                'contact' => $phone,
                'detail' => $details,
                'user_id' => $user->id,
                'workspace_id' => $user->subworkspace?->parent_id,
                'name' => $user->fullname,
                'reason' => 'Soporte Login',
                'status' => 'pendiente',
            ];
        }


        if (!$user) {

            $response = [
                'error' => true,
                'error_msg' => 'Tu usuario no está registrado. Contáctate con tu jefe directo.',
                'data' => null
            ];

        } else if (is_null($dni) || is_null($phone)) {

            $response = [
                'error' => true,
                'error_msg' => 'No se recibieron datos',
                'data' => null
            ];

        } else {

            // $rol = Role::where('name', 'admin')->first();
            // $admins = AssignedRole::query()
            //     ->where('role_id', $rol->id)
            //     ->where('scope', $workspace_id)
            //     ->where('entity_type', User::class)
            //     ->get('entity_id');

            // $users = [];
            // if (!is_null($admins)) {
            //     foreach ($admins as $adm) {
            //         array_push($users, $adm->entity_id . "");
            //     }
            // }

            // $send_users = User::whereIn('id', $users)->get('email');
            // $emails = [];
            // if (!is_null($send_users)) {
            //     foreach ($send_users as $adm) {
            //         array_push($emails, $adm->email);
            //     }
            // }

            if($user->email == $email)
            {
                // Resetea la contraseña del usuario
                $reset_pass = app(UsuarioController::class)->resetPassword($user, new Request());

                // Crea el ticket como solucionado
                $data['status'] = 'solucionado';
                $ticket = Ticket::create($data);

                // Se realiza el login
                $data_login = null;
                $credentials['document'] = $user->document;
                $credentials['password'] = $user->document;

                if(Auth::attempt($credentials))
                {
                    // === verificar el dni como password ===
                    Auth::user()->resetAttemptsUser(); // resetea intentos

                    $data_login = app(AuthController::class)->checkSameDataCredentials($user->document, $user->document, false);
                }

                // Se devuelve el login en la respuesta del api
                $response = [
                    'error' => false,
                    'data' => [
                        'ticket' => $ticket->id,
                        'recovery' => $data_login
                    ]
                ];

            }
            else{
                $ticket = Ticket::create($data);
                $response = [
                    'error' => false,
                    'data' => [
                        'ticket' => $ticket->id,
                        'recovery' => null
                    ]
                ];

            }
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
        $platform_id = Taxonomy::getFirstData('project', 'platform', 'training');
        $preguntas = Post::where('section_id', $tax_id->id)->where('platform_id_onb', $platform_id?->id)->get();

        return response()->json(compact('preguntas'));
    }

    public function existe_email(Request $request, $email = null)
    {
        $existe_email = $email ? User::where('email', $email)->exists() : false;
        $mensaje = '';

        if ($existe_email) {
            $user = User::where('email', $email)->first();
            if ($user->active) {
                $mensaje = 'El usuario está activo.';
                $codigo_http = 200; // OK
            } else {
                $mensaje = 'Tu cuenta se encuentra inactiva. Comunícate con tu coordinador para enviar una solicitud de activación.';
                $codigo_http = 403; // Prohibido
            }
        } else {
            $mensaje = 'El usuario no existe.';
            $codigo_http = 404; // No encontrado
        }

        return response()->json([
            'existe_email' => (bool) $existe_email,
            'mensaje' => $mensaje
        ], $codigo_http);
    }

    public function solicitud_cambio_correo(Request $request)
    {
        $user = Auth::user();
        $email = strip_tags($request->email);

        $response = [
            'error' => true,
            'data' => [
                'ticket' => null
            ]
        ];

        if ($user) {

            $data = [
                'dni' => $user?->document,
                'email' => $email,
                'contact' => $user?->phone_number,
                'detail' => 'Solicitud de cambio de correo',
                'user_id' => $user->id,
                'workspace_id' => $user->subworkspace?->parent_id,
                'name' => $user->fullname,
                'reason' => 'Solicitud de cambio de correo',
                'status' => 'pendiente',
            ];

            $ticket = Ticket::create($data);
            $response = [
                'error' => false,
                'data' => [
                    'ticket' => $ticket->id
                ]
            ];
        }

        return response()->json(compact('response'));
    }

}
