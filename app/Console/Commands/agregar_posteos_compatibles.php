<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PosteoCompas;
use App\Models\Posteo;
use App\Models\Pregunta;

use Illuminate\Support\Facades\DB;

class agregar_posteos_compatibles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:compatibles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->agregrar_posteos_compatible();
        // $this->avance_tablas();
        // $this->generar_json_rpts();
    }
    private function avance_tablas(){
        // pruebas, visitas, ev_abiertas, diplomas, reinicios, encuestas_respuestas
        $users = DB::connection('mysql2')->table('hi_usuarios')->get();
        $m_users = [];
        //FORMAR USUARIOS
        foreach ($users as $user) {
            $m_user[]=[
                ''
            ];
        }
    }
    private function agregrar_posteos_compatible(){
        // $p_compatibles = PosteoCompas::pluck('tema_id','tema_compa_id');
        $posteos = Posteo::all(['nombre','id','curso_id','categoria_id']);
        // $c_posteos = collect($posteos);
        $insert_array = [];
        $this->info('-- Inicio Proceso--');
        $q_proceso = count($posteos);
        $ini= 0;
        foreach ($posteos as $posteo) {
            $this->info($ini.'/'.$q_proceso );
            // $p_compatibles->push($posteo->id);
            // $p_comp = Posteo::where('nombre',$posteo->nombre)->whereNotIn('id',$p_compatibles)->get(['id','nombre','curso_id','categoria_id']);
            $p_comp = Posteo::where('nombre',$posteo->nombre)->with('categoria','categoria.config')->get(['id','nombre','curso_id','categoria_id']);
       
            if(count($p_comp)>0){
                // $p_comp->push($posteo);
                $insert_array[] =  $this->generar_arreglo($p_comp);
            }
            $ini++;
        }
        $this->info('-- Fin Proceso--');

    }
    private function generar_arreglo($array_posteo){
        $new_array = [];
        $q_posteo = count($array_posteo);
        $q_inicio = 0;
        //IDS que no pueden
        foreach ($array_posteo as $value) {
            for ($i=1; $i < $q_posteo ; $i++) { 
                if($value->id != $array_posteo[$i]->id){
                    // VERIFICAR SI EXISTE EL REGISTRO
                    $v_com = PosteoCompas::where('tema_id',$array_posteo[$i]->id)->where('tema_compa_id',$value->id)->first(['id']); 
                    $v_com2 = PosteoCompas::where('tema_id',$value->id)->where('tema_compa_id',$array_posteo[$i]->id)->first(['id']); 
                    if(!($v_com) && !($v_com2)){
                        $post_insert = [
                            [
                                'config_id' => $value->categoria->config->id,
                                'curso_id' => $value->curso_id,
                                'tema_id' => $value->id,
                                'curso_compa_id' => $array_posteo[$i]->curso_id,
                                'tema_compa_id' => $array_posteo[$i]->id,
                            ],
                            [
                                'config_id' => $array_posteo[$i]->categoria->config->id,
                                'curso_compa_id' => $value->curso_id,
                                'tema_compa_id' => $value->id,
                                'curso_id' => $array_posteo[$i]->curso_id,
                                'tema_id' => $array_posteo[$i]->id,
                            ]
                        ];
                        $new_array[] = $post_insert;
                        PosteoCompas::insert($post_insert);                       
                    }
                }
                $q_inicio++;
            }
        }
        return $new_array;
    }
    private function generar_json_rpts(){
        $preguntas = Pregunta::all();
        $q = count($preguntas);
        $this->info('-- Inicio Proceso--');
        $contador = 0;
        foreach ($preguntas as $pregunta) {
            $contador++;
            $d_rpts = json_decode($pregunta->rptas_json,true);
            $rpts_json = array();
            $keys = array_keys($d_rpts);
            foreach ($keys as $key){
                $rpts_json[]=[
                    "id" => $key,
                    "opc" => $d_rpts[$key],
                ];
            }
            $pregunta->rptas_json=json_encode($rpts_json);
            $pregunta->save();
            $this->info($contador.'/'.$q);
        }
        $this->info('-- Fin Proceso--');
    }
}

// 51 52 54 56 
// 51 52 - 51 54  - 51 56
// 52 54 - 52 56 
// 54 - 56 