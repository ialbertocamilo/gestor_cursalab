<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class RegisterUrl extends Model
{
    protected $table = 'register_urls';
    protected $filleable = ['id','url','expiration_date','guest_id','user_id','count_registers'];

    public static function addUrl($data){
        $user = Auth::user();
        $url_exist =  self::where('url',$data['code'])->first();
        if($url_exist){
            return ['message'=>'Esta url ya se encuentra registrada'];
        }
        switch ($data['type_of_time']) {
            case 'month':
                $expiration_date = Carbon::now()->addMonths($data['number_time']);
                break;
            case 'week':
                $expiration_date = Carbon::now()->addWeeks($data['number_time']);
                break;
            case 'day':
                $expiration_date = Carbon::now()->addDays($data['number_time']);
                break;
            default:
                $expiration_date = null;
                break;
        }
        self::create_url($data['code'],$expiration_date,$user->id);
        return ['message'=>'Se registro correctamente'];
    }
    public static function create_url($code,$expiration_date,$user_id,$guest_id = null){
        $id_register_url = RegisterUrl::insertGetId([
            'url'=>$code,
            'expiration_date'=>$expiration_date,
            'user_id' => $user_id,
            'guest_id'=>$guest_id
        ]);
        // if (env('MULTIMARCA')) {
        if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
            SourceMultimarca::insertSource($code,'register',$id_register_url);
        }
    }
    public static function verify_guest_url($url){
        $exist_url =  self::query()
            ->where('url',$url)
            ->select('guest_id','id','expiration_date')
            ->first();

        if ($exist_url && $exist_url->expiration_date) {
            $exist_url = $exist_url->expiration_date > date("Y-m-d G:i")
                ? $exist_url
                : null ;
        }
        $data = $exist_url;

        if ($exist_url) {
            $data['tipo_criterios'] = TipoCriterio::query()
                ->select('id','nombre as name','code_api','can_be_created_by_user', 'data_type', 'parent_id')
                ->with(['criterios' => function($q) {
                    $q->select(
                        'id','tipo_criterio_id','nombre as name', 'parent_id'
                    );
                }])
                ->orderBy('orden')
                ->get();

            $data['email'] = ($exist_url->guest_id)
                ? Guest::where('id',$exist_url->guest_id)->first()->email
                : null ;
        }
        return $data;
    }

    public static function verify_guest_url_multimarca($code){
        $data['source'] =  SourceMultimarca::where('code',$code)->where('type','register')->select('source')->first();
        return $data;
    }

    public static function delete_guest_url($url_id){
        RegisterUrl::where('id',$url_id)->delete();
        if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
            // if (env('MULTIMARCA')) {
            SourceMultimarca::where('type_id',$url_id)->delete();
        }
    }
    public static function register_user($user){
        //Verificar si existe el usuario
        // $user_db = Usuario::where('dni',$user['dni'])->first();
        $user_db = Usuario::where(function($q) use ($user){
            $q->where('dni',$user['dni'])->orWhere('email',$user['email']);
        })->first();
        if($user_db){
            return ['message'=>'El usuario ya se encuentra registrado.'];
        }
        $users_collect = collect();
        $static_headers_api = collect(
            ['module','name','mother_lastname','father_lastname','email','dni']
        );
        $temp_user = [];
        //Obtener módulo del profesor
        $professor_url = RegisterUrl::where('url',$user['code'])->select('user_id','count_registers')->first();
        $module_proffesor = isset($professor_url->user_id) ? ModulosUser::where('user_id',$professor_url->user_id)->with(['config'=>function($q){
            $q->select('id','etapa');
        }])->first() : null;
        $temp_user[] = ($module_proffesor && $module_proffesor->config) ? $module_proffesor->config->etapa : null ;
        $tipo_criterios = TipoCriterio::select('id',DB::raw('upper(nombre) as nombre'),DB::raw('lower(code_api) as code_api'))->get();
        $users_collect->push($static_headers_api->merge($tipo_criterios->pluck('code_api')));
        for ($i=1; $i < count($static_headers_api) ; $i++) {
            $temp_user[] = isset($user[$static_headers_api[$i]]) ? $user[$static_headers_api[$i]] : '' ;
        }
        $user_criterios = collect($user['criterios']);
        foreach ($tipo_criterios as $tc) {
            $user_criterio_valor=$user_criterios->where('tipo_criterio_id',$tc->id)->first();
            $temp_user[] = ($user_criterio_valor) ? $user_criterio_valor['valor'] : null;
        }
        $users_collect->push($temp_user);
        $us = new UsuarioSubirv2();
        info($users_collect);
        $us->collection($users_collect,true,0);
        info($us->errores);
        if($us->q_inserts>0){
            $user_db = Usuario::where('dni',$user['dni'])->first();
            $user_db->password = Hash::make(trim($user['password']));
            $user_db->update();
            if($user['guest_id']){
                Guest::where('email',$user['email'])->update([
                    'state'=>1,
                    'user_id'=>$user_db->id
                ]);
            }else{
                Guest::insert([
                    'email' => $user['email'],
                    'user_id'=>$user_db->id,
                    'date_invitation'=>now(),
                    'state'=>1,
                    'tipo'=>'Por enlace',
                    'admin_id'=>$professor_url->user_id
                ]);
            }
            RegisterUrl::where('url',$user['code'])->update([
                'count_registers'=>$professor_url->count_registers + 1
            ]);
            return  ['message'=>'Te estaremos confirmando cuando tu solicitud haya sido aprobada.'];
        }
        return  ['message'=>'No se pudo registrar al usuario, porfavor pongasé en contacto con el administrador.'];
    }
}
