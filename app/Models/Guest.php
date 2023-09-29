<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\RegisterUrl;
use App\Mail\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
        ->with(['user:id,name,active','status:id,name']);
        if ( $request->q ){
            $query->where( 'email', 'like', "%$request->q%" );
        }
        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        $query->orderBy( $field, $sort );
        return $query->paginate( $request->paginate );
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
        $guest = Guest::insertGuest($email,$status_id_pending,$type_id_by_email,$admin);
        GuestLink::createLink( $code, Carbon::now()->addWeeks( 1 ), $admin->id, $guest->id,0);
        Mail::to( $email )->send( new EmailTemplate( 'emails.guest_invitation', $data ) );
        return [ 'msg'=>'Se ha enviado la invitación.' ];
    }
    public static function insertGuest($email,$status_id,$type_id,$admin){
        $guest = new Guest();
        $guest->email = $email;
        $guest->status_id = $status_id;
        $guest->type_id = $type_id;
        $guest->admin_id = $admin->id;
        $guest->date_invitation = now();
        $guest->workspace_id = get_current_workspace()->id;
        $guest->save();
        return $guest;
    }
    protected function dataShareUrl(){
        $user = Auth::user();
        $share_url = trim(strtolower($user->name));
        //Eliminar multiples espacios y cambiar el espacio por guion
        $url_app_web = DB::table('config_general')->first()->url_app_web;
        $share_url = str_replace(' ','-',preg_replace('/\s+/', ' ', $share_url));
        if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
            
        }
        $urls_generated = RegisterUrl::orderBy('created_at','desc')->whereNull('guest_id')->get();
        $data = [
            'generic_url' => $share_url,
            'app_url' =>  $url_app_web.'/register?c=',
            'urls_generated'=>$urls_generated
        ];
        return $data;
    }
}