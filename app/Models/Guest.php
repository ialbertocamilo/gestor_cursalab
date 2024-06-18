<?php

namespace App\Models;

use Carbon\Carbon;
use App\RegisterUrl;
use App\Mail\EmailTemplate;
use Illuminate\Support\Str;
use App\Models\UsuarioMaster;
use App\Models\Mongo\EmailsSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\UsuarioController;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest extends BaseModel {
    protected $table = 'guests';
    protected $fillable = ['workspace_id','admin_id', 'email', 'user_id', 'status_id', 'date_invitation', 'type_id'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id' );
    }
    public function status() {
        return $this->belongsTo(Taxonomy::class, 'status_id' );
    }
    public static function searchForGrid( $request ) {
        
        $query = self::select( 'id', 'email', 'user_id', 'status_id', 'date_invitation' )
        ->where('workspace_id',get_current_workspace()->id)
        ->with(['user:id,name,lastname,surname,active','status:id,name']);
        if ( $request->q ){
            $query->where( 'email', 'like', "%$request->q%" );
        }
        $field = $request->sortBy ?? 'updated_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        $query->orderBy( $field, $sort );
        return $query->paginate( $request->paginate );
    }
    protected function deleteGuest($guest_ids){
        foreach ($guest_ids as $guest_id) {
            $guest = Guest::where('id',$guest_id)->first();
            if($guest->user_id){
                $user = User::where('id',$guest->user_id)->first();
                if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
                    $usu_master = UsuarioMaster::where('dni', $user->dni)->first();
                    if ($usu_master) {
                        $usu_master->delete();
                    }
                }
                $user->delete();
            }
            $guest->delete();
        }
        $message = count($guest_ids) > 0 ? 'Invitados eliminados' : 'Invitado eliminado'  ;
        return ['message'=> $message];
    }
    protected function sendGuestCodeVerificationByEmail($request){
        $currentRange = env('AUTH2FA_CODE_DIGITS');
        $currentMinutes = env('AUTH2FA_EXPIRE_TIME');

        $email_is_registered = env('MULTIMARCA')
            ? UsuarioMaster::where('email', $request->email)->first()
            : User::where('email', $request->email)->first();

        if ($email_is_registered) {
            return ['message'=>'El email ya se encuentra registrado en la plataforma.','email_sent'=>false];
        }

        $document_is_registered = env('MULTIMARCA')
            ? UsuarioMaster::where('dni', $request->document)->first()
            : User::where('document', $request->document)->first();

        if ($document_is_registered) {
            return ['message'=>'El documento ya se encuentra registrado en la plataforma.','email_sent'=>false];
        }

        $start = '1'.str_repeat('0', $currentRange - 1);
        $end = str_repeat('9', $currentRange);
        $currentCode = rand($start, $end);
        //enviar codigo al email
        $mail_data = [ 'subject' => 'Código de verificación',
                       'code' => $currentCode,
                       'minutes' => $currentMinutes,
                       'name' => trim($request->name).' '. trim($request->lastname),
                       'expires_code'=> Carbon::now()->addMinutes($currentMinutes)->format('Y-m-d H:i:s'),
                    ];
        EmailsSent::storeEmailSent($mail_data,'guest_code_verification');
        Mail::to( trim($request->email) )->send( new EmailTemplate( 'emails.guest_code_verification', $mail_data ) );
        return ['message'=>'Email enviado correctamente.','email_sent'=>true];
    }
    protected function storeGuest($data,$request){
        $guest_link = GuestLink::where('id',$request->code_id)->where('workspace_id',$request->workspace_id)->with('subworkspace:id,name,criterion_value_id')->first();
        if(!$guest_link){
            return ['message'=>'Link inválido'];
        }

        $data['criterion_list_final'] = [];
        foreach ($data['criterion_list'] as $criterion_code => $value_text) {
            $criterion_value = CriterionValue::whereRelation('criterion','code',$criterion_code)
                                ->where('value_text',$value_text)->first();
            if(!$criterion_value){
                $criterion = Criterion::where('code',$criterion_code)->with('field_type:id,code')->select('can_be_create','field_id','id')->first();
                if($criterion?->can_be_create || $criterion?->field_type->code == 'date'){
                    $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
                    $criterion_value = new CriterionValue();
                    $criterion_value[$colum_name] = $value_text;
                    $criterion_value['value_text'] = $value_text;
                    $criterion_value['criterion_id'] = $criterion->id;
                    $criterion_value['active'] = 1;
                    $criterion_value->save();
                }
            }
            $data['criterion_list'][$criterion_code] = $criterion_value?->id;
        }

        $data['active'] = false;
        $current_workspace = get_current_workspace();
        if($guest_link->activate_by_default){
            $data['active'] = $current_workspace->verifyLimitAllowedUsers();
        }
        if($guest_link->subworkspace){
            $data['criterion_list']['module'] =$guest_link->subworkspace->criterion_value_id;
        }
        // Recorrer el array y acceder a las claves y valores de forma dinámica
        foreach ($data['criterion_list'] as $clave => $valor) {
            array_push($data['criterion_list_final'],$valor);
        }
        $status_id_pending = Taxonomy::where('group','guests')->where('type','status')->where('code','registered')->first()?->id;
        if($request->guest_id){
            $_guest = Guest::where('id',$request->guest_id)->first;
            if($_guest){
                $user = User::storeRequest($data);
                $_guest->update([
                    'status_id'=>$status_id_pending,
                    'user_id'=>$user->id
                ]);
            }else{
                return ['message'=>'Link inválido'];
            }
        }else{
            $user = User::storeRequest($data);
            $type_id_by_email = Taxonomy::where('group','guests')->where('type','type')->where('code','by-link')->first()?->id;
            Guest::insertGuest($user->email,$status_id_pending,$type_id_by_email,null,$current_workspace->id,$user->id);
        }
        if (env('MULTIMARCA') && env('APP_ENV') == 'production' && $user) {
            $usuario_controller = new UsuarioController();
            $dni_previo = $user->document;
            $email_previo = $user->email;
            $usuario_controller->crear_o_actualizar_usuario_en_master($dni_previo, $email_previo, $data);
        }
        $guest_link->increment('count_registers', 1);
        $title = 'Tu solicitud ha sido aceptada';
        $message = $data['active'] ? 'Ya puedes ingresar a nuestra plataforma' : 'Recibirás un correo de confirmación cuando se active tu cuenta de capacitación';
        $redirect_login = $data['active'];
        if($data['active']){
            $current_workspace->sendEmailByLimit();
        }
        return compact('title','message','redirect_login');
    }
    protected function verifyGuestCodeVerificationByEmail($request){
        $message = EmailsSent::verifyCode($request);
        return $message;
    }

    public static function sendInvitationByEmail( $email ) {
        $admin = auth()->user();
        $email = trim( $email );

        $info_guest_link = GuestLink::initData();
        $fechaActualNumerica = Carbon::now()->format('Ymd');
        $code = Str::random(14).$fechaActualNumerica;
        $share_url = $info_guest_link[ 'app_url' ].$code;
        $data[ 'enlace' ] = $share_url;
        $data[ 'subject' ] = 'Invitación';
        $data[ 'user' ] = $admin;
        $status_id_pending = Taxonomy::where('group','guests')->where('type','status')->where('code','pending')->first()?->id;
        $type_id_by_email = Taxonomy::where('group','guests')->where('type','type')->where('code','by-email')->first()?->id;
        $guest = Guest::insertGuest($email,$status_id_pending,$type_id_by_email,$admin,get_current_workspace()->id);
        GuestLink::createLink( $code, Carbon::now()->addWeeks( 1 ), $admin->id, $guest->id,0);
        Mail::to( $email )->send( new EmailTemplate( 'emails.guest_invitation', $data ) );
        return [ 'msg'=>'Se ha enviado la invitación.' ];
    }
    public static function insertGuest($email,$status_id,$type_id,$admin,$workspace_id,$user_id=null){
        $guest = new Guest();
        $guest->email = $email;
        $guest->status_id = $status_id;
        $guest->type_id = $type_id;
        $guest->admin_id = $admin?->id;
        $guest->date_invitation = now();
        $guest->workspace_id = $workspace_id;
        $guest->user_id = $user_id;
        $guest->save();
        return $guest;
    }
}
