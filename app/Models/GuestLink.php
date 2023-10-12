<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class GuestLink extends BaseModel
{
    protected $table = 'guest_links';
    protected $filleable = ['id','url','workspace_id','expiration_date','admin_id','guest_id','activate_by_default','count_registers'];
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
    protected function addUrl($data){
        $user = Auth::user();
        $url_exist =  self::where('url',$data['code'])->first();
        if($url_exist){
            return ['msg'=>'Este enlace ya se encuentra registrado.','type_msg'=>'warning'];
        }
        switch ($data['type_of_time']) {
            case 'months':
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
        self::createLink($data['code'],$expiration_date,$user->id,null,$data['activate_by_default']);
        return ['msg'=>'Se creó correctamente el enlace.','type_msg'=>'success'];
    }
    protected function createLink($code,$expiration_date,$admin_id,$guest_id = null,$activate_by_default=false){
        $id_register_url = GuestLink::insertGetId([
            'url'=>$code,
            'workspace_id'=> get_current_workspace()->id,
            'expiration_date'=>$expiration_date,
            'admin_id' => $admin_id,
            'guest_id'=>$guest_id,
            'activate_by_default'=>$activate_by_default
        ]);
        // if (env('MULTIMARCA')) {
        if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
            SourceMultimarca::insertSource($code,'register',$id_register_url);
        }
    }
    protected function verifyGuestUrl($request){
        $ambiente = Ambiente::select('fondo_invitados_app')->first();
        $code = $request->code;
        if(!$code){
            return ['exist_url'=>false,'fondo_invitados_app'=>$ambiente?->fondo_invitados_app,'logo'=>$ambiente?->logo_empresa];
        }
        $guest_link =  self::query()
            ->with(['workspace:id,logo'])
            ->where('url',$code)
            ->select('guest_id','id as code_id','expiration_date','workspace_id')
            ->first();
        if(!$guest_link || $guest_link->expiration_date < date("Y-m-d G:i")){
            $message = $guest_link ? 'Link expirado' : 'El link es incorrecto';
            return ['exist_url'=>false,'fondo_invitados_app'=>$ambiente?->fondo_invitados_app,'message'=>$message,'logo'=>$ambiente?->logo_empresa];
        }
        if ($guest_link && $guest_link->expiration_date) {
            $guest_link = $guest_link->expiration_date > date("Y-m-d G:i")
                ? $guest_link
                : null ;
        }
        $data = $guest_link;
        if ($guest_link) {
            $data['fondo_invitados_app'] =  get_media_url($ambiente?->fondo_invitados_app,'s3');
            $data['logo'] =  get_media_url($guest_link?->workspace?->logo,'s3');
            $criteria_workspace = self::getListCriterion($guest_link);
            $data['personal_criteria_data'] = $criteria_workspace->where('criterion_code','<>','module')->where('personal_data',true)->values()->all();;
            $data['criteria_data'] = $criteria_workspace->where('personal_data',false)->values()->all();
            $data['criteria_data'] = array_merge($criteria_workspace->where('criterion_code','module')->values()->all(),$data['criteria_data']);

            $data['email'] = ($guest_link->guest_id)
                ? Guest::where('id',$guest_link->guest_id)->first()->email
                : null ;
        }
        return $data;
    }
    protected function childCriterionValues($request){
        
        $criterion_values =  CriterionValue::select('id','value_text')->whereHas('workspaces', function ($q) use ($request) {
            $q->where('id', $request->workspace_id);
        })
        ->where('criterion_id', $request->criterion_id)->where('parent_id',$request->criterion_value_id)->orderBy('value_text')->get();

        return ['values' => $criterion_values];
    }
    protected function verify_guest_url_multimarca($code){
        $data['source'] =  SourceMultimarca::where('code',$code)->where('type','register')->select('source')->first();
        return $data;
    }

    protected function deleteGuestLink($url_id){
        GuestLink::where('id',$url_id)->delete();
        if (env('MULTIMARCA') && env('APP_ENV') == 'production') {
            // if (env('MULTIMARCA')) {
            SourceMultimarca::where('type_id',$url_id)->delete();
        }
    }
    
    protected function register_user($user){
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
        $professor_url = GuestLink::where('url',$user['code'])->select('user_id','count_registers')->first();
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
            GuestLink::where('url',$user['code'])->update([
                'count_registers'=>$professor_url->count_registers + 1
            ]);
            return  ['message'=>'Te estaremos confirmando cuando tu solicitud haya sido aprobada.'];
        }
        return  ['message'=>'No se pudo registrar al usuario, porfavor pongasé en contacto con el administrador.'];
    }
    protected function listGuestUrl() {
        $urls_generated = GuestLink::where('workspace_id',get_current_workspace()->id)->orderBy( 'created_at', 'desc' )->whereNull( 'guest_id' )->get();
        return [
            'urls_generated'=> $urls_generated
        ];
    }
    protected function initData(){
        $user = Auth::user();
        $share_url = trim( strtolower( $user->name ) );
        //Eliminar multiples espacios y cambiar el espacio por guion
        $url_app_web = config('app.web_url');
        $share_url = str_replace( ' ', '-', preg_replace( '/\s+/', ' ', $share_url ) );
        return [
            'generic_url' => $share_url,
            'app_url' =>  $url_app_web.'register?c=',
        ];;
    }
    //SUBFUNCTIONS
    private static function getListCriterion($guest_link){
        $criterion_workspace = CriterionWorkspace::with([
            'criterion:id,code,name,field_id,can_be_create,parent_id',
            'criterion.field_type:id,code',
            // 'criterion.values:id,criterion_id,value_text'
        ])
        ->select('criterion_id','criterion_title','avaiable_in_personal_data_guest_form')
        ->where('workspace_id',$guest_link->workspace_id)
        ->where('required_in_user_creation',1)->get()->map(function($criterion_workspace) use ($guest_link){
            $can_be_create = false;
            $criterion_code = $criterion_workspace->criterion->code;
            $criterion_id = $criterion_workspace->criterion_id;
            $criterion_type = $criterion_workspace->criterion->field_type->code;
            $parent_id = $criterion_workspace->criterion->parent_id;
            $values = [];
            //never can be create new modules fron guest form
            if($criterion_code != 'module' && $criterion_code !='document'){
                $can_be_create = boolval($criterion_workspace->criterion->can_be_create);
            }
            if($criterion_type != 'date' && !$parent_id && $criterion_code !='document'){
                $values = CriterionValue::select('id','value_text')->whereHas('workspaces', function ($q) use ($guest_link) {
                    $q->where('id', $guest_link->workspace_id);
                })
                ->where('criterion_id', $criterion_id)->orderBy('value_text')->get();
            }
            return [
                'criterion_id' =>  $criterion_id,
                'parent_id' => $parent_id,
                'criterion_code' => $criterion_code,
                'criterion_type' => $criterion_type,
                'criterion_title' => $criterion_workspace->criterion_title,
                'personal_data' => boolval($criterion_workspace->avaiable_in_personal_data_guest_form),
                'can_be_create' => $can_be_create,
                'values' => $values
            ];
        })->filter(fn($cr) => $cr['criterion_code']<>'document');
        return $criterion_workspace->map(function($cw) use ($criterion_workspace) {
            $child = collect($criterion_workspace)->where('parent_id',$cw['criterion_id'])->first();
            $cw['child_id'] = $child['criterion_id'] ?? null;
            return $cw;
        });
    }
}
