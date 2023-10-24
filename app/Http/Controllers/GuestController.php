<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Guest;
use App\Models\Workspace;
use App\Mail\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\GuestResource;
use App\Http\Requests\GuestStoreRequest;
use App\Http\Controllers\UsuarioController;
use App\Http\Requests\GuestUserStoreRequest;
use App\Http\Requests\GuestInvitationRequest;

class GuestController extends Controller
{
    public function search(Request $request)
    {
        $data = Guest::searchForGrid($request);
        GuestResource::collection($data);
        return $this->success($data);
    }
    public function deleteGuest(Request $request)
    {
        $data = Guest::deleteGuest($request->get('guest_ids'));
        return $this->success($data);
    }

    public function sendInvitation(GuestInvitationRequest $request){
        $data = Guest::sendInvitationByEmail($request->get('email'));
        return $this->success($data);
    }

    public function activateMultipleUsers(Request $request) {

        // $result = Usuario::whereIn('id',  $request->usersIds)->update(['estado' => 1]);
        foreach ($request->users_id as $user_id) {
            $user = User::with('subworkspace:id,name')->where('id',$user_id)->first();
            if($user){
                $this->changeStatusUser($user);
            }
        }
        return $this->success(['message' => 'Estado actualizado correctamente.']);
    }
    public function limitsWorspace(){
        $data = Workspace::infoLimitCurrentWorkspace();
        return $this->success($data);
    }

    public function editGuestUser(Guest $guest){
        $guest->load('user');
        $user = $guest->user;
        $user->load('criterion_values');
        $user_controller = New UsuarioController();
        $current_workspace_criterion_list = $user_controller->getFormSelects(true);
        $user_criteria = [];

        foreach ($current_workspace_criterion_list as $criterion) {

            $value = $user->criterion_values->where('criterion_id', $criterion->id);

            $user_criterion_value = $criterion->multiple ? $value->pluck('id') : $value?->first()?->id;

            if ($criterion->field_type?->code == 'date') {
                $user_criteria[$criterion->code] = $value?->first()?->value_text;
            } else {
                $user_criteria[$criterion->code] = $user_criterion_value;
            }
        }

        $user->criterion_list = $user_criteria;

        return $this->success([
            'usuario' => $user,
            'criteria' => $current_workspace_criterion_list
        ]);
    }

    public function updateGuestUser(GuestUserStoreRequest $request, User $user){
        
        try{
            $data = $request->validated();
            /****************** Insertar/Actualizar en BD master ****************/
            if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
                $usuario_controller = new UsuarioController();
                $dni_previo = $user['document'];
                $email_previo = $user['email'];
                $user_controller->crear_o_actualizar_usuario_en_master($dni_previo, $email_previo, $data);
                // info($user);
            }
            /********************************************************************/
            User::storeRequest($data, $user);

            return $this->success(['msg' => 'Usuario actualizado correctamente.']);
        }  catch (\Exception $e){
                $user_controller = New UsuarioController();
                return $user_controller->master_errors($e);
        }
    }

    public function statusGuestUser(Guest $guest){
        $guest->load('user');
        $user = $guest->user;
        $this->changeStatusUser($user);
        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }
    private function changeStatusUser($user){
        $status = !$user->active;
        $current_workspace = get_current_workspace();
        if ($status && !$current_workspace->verifyLimitAllowedUsers()){
            $error_msg = config('errors.limit-errors.limit-user-allowed');
            return $this->error($error_msg, 422);
        }
        if($status){
            $user->loadMissing('subworkspace');
            $data = [
                'lastname'=>$user->lastname,
                'name'=>$user->name,
                'document'=>$user->document,
                'subworkspace_name'=>$user->subworkspace->name,
                'subject'=>'Correo de bienvenida',
                'web_url' => config('app.web_url')
            ];
            Mail::to( trim($user->email) )->send( new EmailTemplate( 'emails.welcome_user', $data ) );
        }
        $user->update(['active' => $status]);
        $current_workspace->sendEmailByLimit();
    }
}
