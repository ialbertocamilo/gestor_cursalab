<?php

namespace App\Models;

use App\Carrera;
use App\CambiosMasivos;
use App\UsuariosMasivos;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsuariosFarmaHistorial implements ToCollection
{
    private $q_cesados=0;
    private $q_errors = 0;
    public function collection(Collection $rows) {
        $errores = [];
        $total = count($rows);
        // PROCESAR CADA DNI
        for ($i=3; $i < $total; $i++) {
            if(!empty($rows[$i][1])){
                $dni = $rows[$i][2];
                $carrera = trim($rows[$i][8]);
                $user = Usuario::select('id')->where('dni', $dni)->first();
                if($user){
                    $u_antiguo = DB::connection('mysql2')->table('hi_usuarios')->select('usuario_id')->where('dni', $dni)->first();
                    $u_id_nuevo =$user->id;
                    $u_id_antiguo = $u_antiguo->usuario_id;
                    $config_id = $user->config_id;
                    $this->recuperar_data_x_dni($dni, $u_id_antiguo, $u_id_nuevo,$config_id);
                    //COMPARAR ID'S CARRERA
                    $carr_exl = Carrera::where('nombre',$carrera)->first(['id']);
                    $matricula = Matricula::where('usuario_id',$user->id)->where('presente',1)->first();
                    if($carr_exl->id != $matricula->carrera_id){
                        //MIGRAR CARRERA
                        $rows[$i][10]='Cambio de módulo';
                        $rows[$i]['migra_from_farma_historial']=true;
                        $usu_migra =  new CambiosMasivos();
                        $usu_migra ->collection($rows[$i]);
                    }
                }else{
                    // // SI NO EXISTE EL DNI SE CREA CON SU MATRICULA
                    // $rows[$i][10]='Nuevo';
                    // $rows[$i]['new_desde_farma_historial']=true;
                    // $usu_masivo =  new UsuariosMasivos();
                    // $usu_masivo->collection($rows[$i]);
                    // $user = Usuario::select('id')->where('dni', $dni)->first();
                    // $u_antiguo = DB::connection('mysql2')->table('hi_usuarios')->select('usuario_id')->where('dni', $dni)->first();
                    // //VERIFICA SI EXISTE EN FARMA HISTORIAL
                    // if($u_antiguo){
                    //     $this->recuperar_data_x_dni($dni, $u_antiguo->usuario_id, $user->id,$user->config_id);
                    // }else{
                    //ERRORES
                    $error =[];
                    array_push($error,['tipo'=>'dni_error','celda'=>'D'.($i+1),'mensajes'=>'Dni no existe en farma historial']);
                    $errores[]=[
                        'data' =>[
                            'dni' => $dni,
                            'nombre' => $rows[$i][3],
                        ],
                        'error' => $error
                    ];
                    // }
                }
            }
        }
        $this->q_errors = count($errores);
        foreach ($errores as $err) {
            DB::table('err_masivos')->insert([
                'err_data'=> json_encode($err['data']),
                'err_type' => json_encode($err['error']),
                'type_masivo' => 'farma_historial'
            ]);
        }
    }
    public function recuperar_data_x_dni($dni, $u_id_antiguo, $u_id_nuevo,$config_id){
        $db_name = $this->getDefaultDBName();
        $hi_db_name = $this->getHistorialDBName();
        // diplomas
        $diplomas = DB::connection('mysql2')->table('hi_diplomas')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($diplomas as $row) {
            $existe = Diploma::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('curso_id', $row->curso_id)->first();
            if(!$existe){
                $new = new Diploma;
                $new->usuario_id = $u_id_nuevo;
                $new->curso_id = $row->curso_id;
                $new->fecha_emision = $row->fecha_emision;
                $new->save();
            }
        }

        // encuestas_respuestas
        $encuestas_respuestas = DB::connection('mysql2')->table('hi_encuestas_respuestas')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($encuestas_respuestas as $row) {
            $existe = PollQuestionAnswer::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('curso_id', $row->curso_id)->first();
            if(!$existe){
                $new = new PollQuestionAnswer;
                $new->usuario_id = $u_id_nuevo;
                $new->encuesta_id = $row->encuesta_id;
                $new->curso_id = $row->curso_id;
                $new->pregunta_id = $row->pregunta_id;
                $new->tipo_pregunta = $row->tipo_pregunta;
                $new->save();
            }
        }
        // ev_abiertas
        $ev_abiertas = DB::connection('mysql2')->table('hi_ev_abiertas')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($ev_abiertas as $row) {
            $existe = Ev_abierta::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('posteo_id', $row->posteo_id)->first();
            if(!$existe){
                $new = new Ev_abierta;
                $new->usuario_id = $u_id_nuevo;
                $new->categoria_id = $row->categoria_id;
                $new->curso_id = $row->curso_id;
                $new->posteo_id = $row->posteo_id;
                $new->usu_rptas = $row->usu_rptas;
                $new->save();
            }
        }

        // pruebas
        $pruebas = DB::connection('mysql2')->table('hi_pruebas')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($pruebas as $row) {
            $existe = Prueba::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('posteo_id', $row->posteo_id)->first();
            if(!$existe){
                $new = new Prueba;
                $new->usuario_id = $u_id_nuevo;
                $new->categoria_id = $row->categoria_id;
                $new->curso_id = $row->curso_id;
                $new->posteo_id = $row->posteo_id;
                $new->intentos = $row->intentos;
                $new->rptas_ok = $row->rptas_ok;
                $new->rptas_fail = $row->rptas_fail;
                $new->nota = $row->nota;
                $new->resultado = $row->resultado;
                $new->usu_rptas = $row->usu_rptas;
                $new->save();
            }
        }

        // reinicios
        $reinicios = DB::connection('mysql2')->table('hi_reinicios')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($reinicios as $row) {
            $existe = Reinicio::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('posteo_id', $row->posteo_id)->first();
            if(!$existe){
                $new = new Reinicio;
                $new->usuario_id = $u_id_nuevo;
                $new->curso_id = $row->curso_id;
                $new->posteo_id = $row->posteo_id;
                $new->admin_id = $row->admin_id;
                $new->tipo = $row->tipo;
                $new->acumulado = $row->acumulado;
                $new->save();
            }
        }

        // // resumen_general
        // $resumen_general = DB::connection('mysql2')->table('hi_resumen_general')->where('usuario_id', $u_id_antiguo)->get();
        // foreach ($resumen_general as $row) {
        //     $existe = Resumen_general::select('usuario_id')->where('usuario_id', $u_id_nuevo)->first();
        //     if(!$existe){
        //         $new = new Resumen_general;
        //         $new->usuario_id = $u_id_nuevo;
        //         $new->cur_asignados = $row->cur_asignados;
        //         $new->tot_completados = $row->tot_completados;
        //         $new->nota_prom = $row->nota_prom;
        //         $new->intentos = $row->intentos;
        //         $new->rank = $row->rank;
        //         $new->porcentaje = $row->porcentaje;
        //         $new->save();
        //     }
        // }

        // // resumen_x_curso
        // $resumen_x_curso = DB::connection('mysql2')->table('hi_resumen_x_curso')->where('usuario_id', $u_id_antiguo)->get();
        // foreach ($resumen_x_curso as $row) {
        //     $existe = Resumen_x_curso::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('curso_id', $row->curso_id)->first();
        //     if(!$existe){
        //         $new = new Resumen_x_curso;
        //         $new->usuario_id = $u_id_nuevo;
        //         $new->curso_id = $row->curso_id;
        //         $new->categoria_id = $row->categoria_id;
        //         $new->asignados = $row->asignados;
        //         $new->aprobados = $row->aprobados;
        //         $new->realizados = $row->realizados;
        //         $new->revisados = $row->revisados;
        //         $new->desaprobados = $row->desaprobados;
        //         $new->nota_prom = $row->nota_prom;
        //         $new->intentos = $row->intentos;
        //         $new->visitas = $row->visitas;
        //         $new->estado = $row->estado;
        //         $new->porcentaje = $row->porcentaje;
        //         $new->save();
        //     }
        // }

        // usuario_uploads
        $usuario_uploads = DB::connection('mysql2')->table('hi_usuario_uploads')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($usuario_uploads as $row) {
            $existe = Usuario_upload::select('usuario_id')->where('usuario_id', $u_id_nuevo)->first();
            if(!$existe){
                $new = new Usuario_upload;
                $new->usuario_id = $u_id_nuevo;
                $new->link = $row->link;
                $new->description = $row->description;
                $new->file = $row->file;
                $new->save();
            }
        }

        // usuario_versiones
        $usuario_versiones = DB::connection('mysql2')->table('hi_usuario_versiones')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($usuario_versiones as $row) {
            $existe = Usuario_version::select('usuario_id')->where('usuario_id', $u_id_nuevo)->first();
            if(!$existe){
                $new = new Usuario_version;
                $new->usuario_id = $u_id_nuevo;
                $new->v_android = $row->v_android;
                $new->v_ios = $row->v_ios;
                $new->save();
            }
        }

        // visitas
        $visitas = DB::connection('mysql2')->table('hi_visitas')->where('usuario_id', $u_id_antiguo)->get();
        foreach ($visitas as $row) {
            $existe = Visita::select('usuario_id')->where('usuario_id', $u_id_nuevo)->where('post_id', $row->post_id)->first();
            if(!$existe){
                $new = new Visita;
                $new->usuario_id = $u_id_nuevo;
                $new->curso_id = $row->curso_id;
                $new->post_id = $row->post_id;
                $new->sumatoria = $row->sumatoria;
                $new->tipo_tema = $row->tipo_tema;
                $new->estado_tema = $row->estado_tema;
                $new->save();
            }
        }
        //ACTUALIZAR RESUMENES
        $this->actualizar_resumenes($u_id_nuevo,$config_id);
    }
    private function actualizar_resumenes($u_id_nuevo,$config_id){
         // CONSULTAR LOS CURSOS MATRICULADOS
         $helper = new HelperController();
         $curso_ids = $helper->help_cursos_x_matricula($u_id_nuevo);
         //ACTUALIZAR TABLAS RESUMENES
         $ab_config = Abconfig::where('id',$config_id)->first(['mod_evaluaciones']);
         $rest_avance = new RestAvanceController();
         $mod_eval = json_decode($ab_config->mod_evaluaciones, true);
         foreach ($curso_ids as $cur_id) {
            // ACTUALIZAR RESUMENES
            $rest_avance->actualizar_resumen_x_curso($user['usuario_id'],$cur_id, $mod_eval['nro_intentos']);
         }
         $rest_avance->actualizar_resumen_general($user['usuario_id']);
    }
    public function get_q_cesados()
    {
        return $this->q_cesados;
    }
    public function get_q_errors(){
        return $this->q_errors;
    }
}
