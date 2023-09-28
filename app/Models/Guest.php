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
    protected $fillable = [ 'admin_id', 'email', 'user_id', 'state', 'date_invitation', 'tipo' ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id' );
    }
    public static function searchForGrid( $request ) {
        $user = Auth::user();
        $query = self::select( 'id', 'email', 'user_id', 'state', 'date_invitation' )
        // ->where( 'admin_id', $user->id )
        ->with( [
            'user'=>function( $q ) {
                $q->select( 'id', 'name', 'active' );
            }
        ] );
        if ( $request->q )
        $query->where( 'email', 'like', "%$request->q%" );
        // if ( $request->modulo )
        //     $query->where( 'config_id', 'like', "%$request->modulo%" );
        // $query->latest();
        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        $query->orderBy( $field, $sort );
        return $query->paginate( $request->paginate );
    }
   
    public static function send_email_invitation( $email ) {
        $email = trim( $email );
        $user = Auth::user();
        $search_email = Guest::where( 'admin_id', $user->id )->where( 'email', $email )->first();
        if ( isset( $search_email ) ) {
            return [ 'msg'=>'Este email ya ha sido invitado.' ];
        }
        $usuario_bd = Usuario::where( 'email', $email )->first();
        if ( isset( $usuario_bd ) ) {
            return [ 'msg'=>'Este email ya se encuentra registrado.' ];
        }
        $url_guest = self::data_share_url();
        $code = Str::random( 14 );
        $share_url = $url_guest[ 'app_url' ].$code;
        $data[ 'enlace' ] = $share_url;
        $data[ 'subject' ] = 'Invitación';
        $data[ 'user' ] = Auth::user();
        Mail::to( $email )->send( new EmailTemplate( 'emails.send_invitation', $data ) );
        $guest_id = Guest::insertGetId( [
            'email' => $email,
            'date_invitation'=>now(),
            'state'=>0,
            'tipo'=>'Por invitación',
            'admin_id'=>$user->id
        ] );
        RegisterUrl::create_url( $code, Carbon::now()->addWeeks( 1 ), $user->id, $guest_id );
        return [ 'msg'=>'Se ha enviado la invitación.' ];
    }
}