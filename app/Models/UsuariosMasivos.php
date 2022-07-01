<?php

namespace App\Models;

use App\Cargo;
use App\Ciclo;
use App\Grupo;
use App\Botica;
use App\Carrera;
use App\Usuario;
use App\Abconfig;
use App\Criterio;
use App\Matricula;
use App\Resumen_general;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Http\Controllers\ApiRest\HelperController;

class UsuariosMasivos  implements ToCollection
{
    private $q_inserts = 0;
    private $q_updates = 0;
    private $q_errors = 0;
    private $data_errors = null;
    private $data_type_errors = null;

    private $q_generar_matricula = [];

    public function collection(Collection $rows)
    {
        set_time_limit(-1);
        $total = count($rows);
        $modulos = Abconfig::with('carreras')->get(['id','etapa','codigo_matricula']);
        $criterios = Criterio::all();
        $boticas = Botica::with('criterio')->get();
        $cargos = Cargo::all();
        $ciclos = Ciclo::all();
        $date_code = date('dmy');
        $grupos = $this->setAndGetGruposDeSistema($rows, $modulos, $date_code);

        $errores = [];
        $update_usuarios=[];
        //I=3 por que los datos del excel comienzan en la fila 4
        for ($i=1; $i < $total ; $i++) {
            //VERIFICAR FILAS VACIAS
            if(!empty($rows[$i][1])){
                /* 
                    Columnas del excel: Módulo(0),Área(1),Sede(2),DNI(3),Apellidos y Nombres(4),Genero(5),Carrera(6),Ciclo(7),Cargo(8)
                */
                $modulo = trim($rows[$i][0]); //config id es el id del modulo
                $grupo = trim($rows[$i][1]);
                $botica = trim($rows[$i][2]);
                $dni = trim($rows[$i][3]);
                $nombre = trim($rows[$i][4]);
                // $grupo =strtoupper(trim($rows[$i][5]));
                $sexo = trim($rows[$i][5]);
                $carrera = trim($rows[$i][6]);
                $ciclo = trim($rows[$i][7]);
                $cargo = trim($rows[$i][8]);
                $accion='Nuevo';
                // $accion = trim($rows[$i][8]);
                //VERIFICAR DATOS VACIOS
                $nom_ety = empty($nombre);
                $bot_ety = empty($botica);
                $car_ety = empty($cargo);
                $sex_ety = empty($sexo);
                $datos_empty = false;
                if($nom_ety || $bot_ety || $car_ety || $sex_ety){
                    $datos_empty = true;
                }

                //BUSCAR CARGO
                $carg_id = null;
                $carg = $cargos->where('nombre',$cargo)->first();
                if($carg){
                    $carg_id = $carg->id;
                }
                $us_dup = null; //para verificar si existe duplicado de usuario en caso de ser nuevo
                $us_exi = null; // para verificar que existe el usuario que se actualizara
                $dni_length  = strlen($dni); // verificar el dni
                $sexo_length  = strlen($sexo);
                //BUCAR LOS IDS DE CARRERA y MODULO
                $mod_id=null;
                $carr_id = null;
                $ciclo_id = null;
                foreach ($modulos as $mod) {
                    if($mod->etapa==$modulo){
                        $mod_id = $mod->id;
                        foreach ($mod->carreras as $carr) {
                            if($carr->nombre==$carrera){
                                $carr_id = $carr->id;
                                $codigo_matricula = $mod->codigo_matricula;
                            }
                        }
                    }
                }
                //BUSCAR ID GRUPO
                $b_grupo = $criterios->whereIn('valor', [$grupo, strtoupper($grupo), strtolower($grupo), ucwords($grupo), ucfirst($grupo)])
                            ->where('config_id', $mod_id)
                            ->first();
                $grupo_id = null;
                if($b_grupo){
                    $grupo_id = $b_grupo->id; //GRUPO ID
                }
                //BUSCAR BOTICA
                $botica_id = null;
                $bot =  $boticas->where('nombre',$botica)->where('criterio_id',$grupo_id)->where('config_id', $mod_id)->first();
                if($bot){
                    $botica_id = $bot->id;
                }
                //VERIFICAR SI EXISTE DUPLICIDAD
                $user_b = Usuario::where('dni',$dni)->first(['id','grupo','dni']);
                if($accion=='Nuevo' && ($user_b)){
                    $us_dup = $user_b;
                }
                if($accion=='datos' && ($user_b)){
                    $us_exi = $user_b;
                }
                $tipos_acciones = array_column(config('constantes.acción.select'),'nombre');
                $existe_accion = in_array($accion,$tipos_acciones);
                if($carr_id){
                    $b_ciclo = $ciclos->where('carrera_id',$carr_id)->where('nombre',$ciclo)->first();
                    if($b_ciclo){
                        //AÑADIR USUARIO
                        $ciclo_id = $b_ciclo->id;
                        if($carg_id && $grupo_id && $botica_id && ($dni_length>=8) && !($datos_empty)){
                            //VERIFICAR CURRICULA
                            $grupo = strtoupper($grupo);
                            $user = [
                                'config_id' => $mod_id,
                                'codigo_matricula' =>$codigo_matricula,
                                'grupo_id' => $grupo_id,
                                'grupo_nombre' => $grupo,
                                'nombre' => $nombre,
                                'cargo' => $cargo,
                                'dni' => $dni,
                                'sexo' => $sexo,
                                'botica' => $botica,
                                'botica_id' => $botica_id,
                                'grupo' => $grupo,
                                'password' => Hash::make($dni),
                                'carrera_id' => $carr_id,
                                'ciclo_id' => $ciclo_id,
                                'secuencia' => $b_ciclo->secuencia
                            ];
                            switch ($accion) {
                                case 'Nuevo':
                                    if(!($us_dup)){
                                        $this->crear_nuevo_usuario($user, $ciclos, $grupos, $date_code);
                                        $this->q_inserts ++ ;
                                    }
                                break;
                                case 'datos':
                                    if($us_exi){
                                        $this->actualizar_usuario($user,$us_exi);
                                        $this->q_updates ++ ;
                                        $update_usuarios[]=$us_exi->id;
                                    }
                                break;
                                default:
                                break;
                            }
                        }
                    }
                }
                if(!($grupo_id) || !($botica_id) || !($carg_id) || !($mod_id) || !($carr_id) || !($ciclo_id) || ($us_dup && $accion=='Nuevo') || (!($us_exi) && $accion=='datos') || ($dni_length<8) || ($sexo_length>1) || empty($accion) || ($datos_empty) || !($existe_accion)){
                    //ERRORES
                    $error =[];
                    if(!$existe_accion){
                        array_push($error,['tipo'=>'acción_error','celda'=>'F'.($i+1),'mensajes'=>'Acción no encontrada']);
                    }
                    if(!($grupo_id)){
                        array_push($error,['tipo'=>'grupo_error','celda'=>'F'.($i+1),'mensajes'=>'Grupo no encontrado']);
                    }
                    if(!($botica_id)){
                        if($bot_ety){
                            array_push($error,['tipo'=>'botica_error','celda'=>'C'.($i+1),'mensajes'=>'Botica vacia']);
                        }else{
                            array_push($error,['tipo'=>'botica_error','celda'=>'F'.($i+1),'mensajes'=>'Botica no encontrada']);
                        }
                    }
                    if(!($carg_id)){
                        if($car_ety){
                            array_push($error,['tipo'=>'cargo_error','celda'=>'C'.($i+1),'mensajes'=>'Cargo vacio']);
                        }else{
                            array_push($error,['tipo'=>'cargo_error','celda'=>'F'.($i+1),'mensajes'=>'Cargo no encontrado']);
                        }
                    }
                    if(!($mod_id)){
                        array_push($error,['tipo'=>'modulo_error','celda'=>'B'.($i+1),'mensajes'=>'Módulo no encontrado']);
                    }else{
                        if(!($carr_id)){
                            array_push($error,['tipo'=>'carrera_error','celda'=>'I'.($i+1),'mensajes'=>'Carrera no encontrado']);
                        }else{
                            if(!($ciclo_id)){
                                array_push($error,['tipo'=>'ciclo_error','celda'=>'J'.($i+1),'mensajes'=>'Ciclo no encontrado']);
                            }
                        }
                    }
                    if(!empty($us_dup) && $accion == 'Nuevo'){
                        array_push($error,['tipo'=>'dni_error','celda'=>'C'.($i+1),'mensajes'=>'Dni duplicado']);
                    }
                    if(empty($us_exi) && $accion == 'datos'){
                        array_push($error,['tipo'=>'dni_error','celda'=>'C'.($i+1),'mensajes'=>'Dni no encontrado']);
                    }
                    if($sexo_length>1){
                        array_push($error,['tipo'=>'género_error','celda'=>'C'.($i+1),'mensajes'=>'Género vacio']);
                    }
                    if($sex_ety){
                        array_push($error,['tipo'=>'género_error','celda'=>'C'.($i+1),'mensajes'=>'Género no encontrado']);
                    }
                    if($dni_length < 8){
                        array_push($error,['tipo'=>'dni_error','celda'=>'C'.($i+1),'mensajes'=>'Tiene que tener 8 o más digitos']);
                    }
                    if(empty($accion)){
                        array_push($error,['tipo'=>'acción_error','celda'=>'C'.($i+1),'mensajes'=>'Acción no encontrado']);
                    }
                    if($datos_empty){
                        if($nom_ety){
                            array_push($error,['tipo'=>'nombre_error','celda'=>'C'.($i+1),'mensajes'=>'Nombre vacio']);
                        }
                    }
                    $errores[]=[
                        'data' =>[
                            'modulo' => $modulo,
                            'config_id' => $mod_id,
                            'dni' => $dni,
                            'nombre' => $nombre,
                            'botica' => $botica,
                            'botica_id'=>$botica_id,
                            'grupo' => $grupo,
                            'grupo_id' => $grupo_id,
                            'cargo' => $cargo,
                            'sexo' => $sexo,
                            'carrera' => $carrera,
                            'carrera_id'=>$carr_id,
                            'ciclo' => $ciclo,
                            'ciclo_id' => $ciclo_id,
                            'accion' =>$accion
                        ],
                        'error' => $error,
                        'err_data_original'=>$rows[$i],
                    ];
                    $this->q_errors ++ ;
                }
            }
        }
        if(count($this->q_generar_matricula)>0){
            $chunks = array_chunk($this->q_generar_matricula, 500);
            foreach ($chunks as $rs) {
                DB::table('resumen_general')->insert($rs);
            }
        }
        if(count($update_usuarios)>0){
            DB::table('update_usuarios')->insert([
                'usuarios_id' => json_encode($update_usuarios),
                'tipo' => 'update_resumenes_from_masivo',
            ]);
        }
        // $this->q_errors = count($errores);
        $type_masivo = (isset($rows[0]['new_desde_farma_historial'])) ? 'farma_historial' : 'usuarios';
        foreach ($errores as $err) {
            $this->data_errors[] = $err['data'];
            $this->data_type_errors[] = $err['error'];
            DB::table('err_masivos')->insert([
                'err_data'=> json_encode($err['data'],JSON_UNESCAPED_UNICODE),
                'err_type' => json_encode($err['error'],JSON_UNESCAPED_UNICODE),
                'err_data_original' =>json_encode($err['err_data_original'],JSON_UNESCAPED_UNICODE),
                'type_masivo' => $type_masivo
            ]);
        }
    }

    public function crear_nuevo_usuario($usuario, $ciclos, $grupos, $date_code){
        //CREAR CODIGO
        $code = strtoupper($usuario["codigo_matricula"]) . '-' . $date_code;
        //Crear grupo
        $gr  = $grupos->where('nombre',$code)->first();

        //Crear USUARIO
        $user = new Usuario();
        $user->config_id = $usuario['config_id'];
        $user->grupo_id = $gr->id;
        $user->perfil_id = 9;
        $user->nombre = $usuario['nombre'];
        $user->cargo = $usuario['cargo'];
        $user->dni = $usuario['dni'];
        $user->sexo = $usuario['sexo'];
        $user->botica_id = $usuario['botica_id'];
        $user->botica = $usuario['botica'];
        $user->grupo = $usuario['grupo_id'];
        $user->grupo_nombre = $usuario['grupo_nombre'];
        $user->password = $usuario['password'];
        $user->save();
        //Matricular
        $us_ciclos = $ciclos->where('carrera_id', $usuario['carrera_id'])->where('estado',1);
        foreach ($us_ciclos as $ci) {
            if($ci->secuencia != 0 || $usuario['secuencia'] == 0 ){
                $mat = new Matricula();
                $mat->usuario_id = $user->id;
                $mat->carrera_id= $usuario['carrera_id'];
                $mat->ciclo_id=  $ci->id;
                $mat->secuencia_ciclo = $ci->secuencia;
                if($ci->id == $usuario['ciclo_id']){
                    $mat->presente=1;
                }else{
                    $mat->presente=0;
                }
                if($ci->secuencia <= $usuario['secuencia']){
                    $mat->estado =1;
                }else{
                    $mat->estado =0;
                }
                $mat->save();
                //Crear Matricula Criterio
                Matricula_criterio::insert([
                    'matricula_id' => $mat->id,
                    'criterio_id' => $usuario['grupo_id'],
                ]);
            }
        }
        $helper = new HelperController();
        $this->q_generar_matricula [] = [
            'usuario_id' => $user->id,
            'cur_asignados' => count($helper->help_cursos_x_matricula($user->id)),
        ];
        return $user->id;
    }

    public function actualizar_usuario($usuario,$usu){
        $matriculas = Matricula::where('usuario_id',$usu->id)->select('id')->get()->pluck('id');
        Matricula_criterio::whereIn('matricula_id',$matriculas)->update([
            'criterio_id' => $usuario['grupo_id']
        ]);

        return Usuario::where('dni',$usuario['dni'])->update([
            // 'botica' => $usuario['botica'],
            // 'botica_id' => $usuario['botica_id'],
            'sexo' => $usuario['sexo'],
            'cargo' => $usuario['cargo'],
            'nombre' => $usuario['nombre'],
            // 'grupo' => $usuario['grupo_id'],
            // 'grupo_nombre' => $usuario['grupo_nombre']
        ]);
    }

    public function get_qinsert()
    {
        return $this->q_inserts;
    }
    public function get_q_errors(){
        return $this->q_errors;
    }
    public function get_q_updates(){
        return $this->q_updates;
    }
    public function get_data_errors(){
        return $this->data_errors;
    }
    public function get_data_type_errors(){
        return $this->data_type_errors;
    }

    protected function setAndGetGruposDeSistema($rows, $modulos, $date_code)
    {
        // $column_action = 10;
        // $actions = $rows->pluck($column_action)->unique()->all();

        // si en la importación hay acciones para crear
        // if ( in_array('Nuevo', $actions) || in_array('nuevo', $actions) || in_array('NUEVO', $actions)  )
        // {
            // deben insertarse los nuevos grupos de ser necesario
            foreach($modulos AS $modulo)
            {
                $codigo = $modulo->codigo_matricula . '-' . $date_code;
                Grupo::firstOrCreate(['nombre' => $codigo, 'estado' => 1]);
            }
        // }

        return Grupo::whereDate('created_at', date('Y-m-d'))->where('estado', 1)->get();
    }
}
