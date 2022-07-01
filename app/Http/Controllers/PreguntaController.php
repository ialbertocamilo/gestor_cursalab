<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Pregunta;
use Illuminate\Http\Request;
use App\Http\Requests\PreguntaStoreRequest;
use App\Http\Requests\ExamenImportExcelRequest;
use stdClass;

class PreguntaController extends Controller
{

    // public function index(Posteo $posteo, Request $request)
    // {
    //     if ($request->has('q')) {
    //         $question = $request->input('q');
    //         // return $question;
    //         $preguntas = Pregunta::where('pregunta', 'like', '%'.$question.'%')->paginate();
    //     }else{
    //         $preguntas = Pregunta::paginate();
    //     }
    //     return view('preguntas.index', compact('preguntas', 'posteo'));
    // }


    public function create(Posteo $posteo)
    {
        $post_array = Posteo::select('id', 'nombre')->where('id', $posteo->id)->pluck('nombre', 'id');

        $tipo_preg = 'selecciona';
        if ($posteo->tipo_ev == 'abierta') {
            $tipo_preg = 'texto';
        }
        return view('posteos.vue_create_update', compact('post_array', 'posteo', 'tipo_preg'));
    }


    public function store(Posteo $posteo, PreguntaStoreRequest $request)
    {
        $pregunta = Pregunta::create($request->all());

        // Si tema no tiene EV (preguntas) entonces debe ser NO-EVALUABLE
        $tema_preguntas = Pregunta::where('post_id', $posteo->id)->where('estado', '1')->first();
        if ($tema_preguntas) {
            $posteo->evaluable = 'si';
            $posteo->tipo_ev = 'calificada';
        } else {
            $posteo->evaluable = 'no';
            $posteo->tipo_ev = '';
        }
        $posteo->save();

        ////// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $posteo->curso_id)->where('evaluable', 'si')->first();
        $curso = $posteo->curso;
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////

        return redirect()->route('posteos.preguntas', [$posteo->curso->id, $posteo->id])
            ->with('info', 'Registro creado con éxito');
    }


    public function edit(Posteo $posteo, Pregunta $pregunta, Request $request)
    {
        $post_array = Posteo::select('id', 'nombre')->where('id', $posteo->id)->pluck('nombre', 'id');

        $tipo_preg = 'selecciona';
        if ($posteo->tipo_ev == 'abierta') {
            $tipo_preg = 'texto';
        }
        $pregunta->rptas_json = json_decode($pregunta->rptas_json);
//        dd($pregunta->rptas_json);
        $temp = collect();
        if($pregunta->rptas_json){
            foreach ($pregunta->rptas_json as $key => $rpta) {
                $temp->push([
                    'id' => (int)$key,
                    'opc' => $rpta,
                    'correcta' => $pregunta->rpta_ok === (int)$key
                ]);
            }
        }
        $pregunta->rptas_json = $temp;

        return view('posteos.vue_create_update', compact('pregunta', 'post_array', 'posteo', 'tipo_preg'));
    }

    public function getInitalData($pregunta_id)
    {
        $pregunta = Pregunta::find($pregunta_id);
        if ($pregunta) {
            $pregunta->rptas_json = json_decode($pregunta->rptas_json, true);
            $temp = collect();
            if($pregunta->rptas_json){
                foreach ($pregunta->rptas_json as $key => $rpta) {
                    $temp->push([
                        'id' => (int)$key,
                        'opc' => $rpta,
                        'correcta' => $pregunta->rpta_ok === (int)$key
                    ]);
                }
            }
            $pregunta->rptas_json = $temp;
            $pregunta->curso_id = $pregunta->posteo->curso_id;

        } else {
            $pregunta = new stdClass();
            $pregunta->estado = true;
            $pregunta->pregunta = '';
            $pregunta->rpta_ok = 0;
            $pregunta->rptas_json = [];
        }

        return response()->json(['pregunta' => $pregunta], 200);
    }

    public function createOrUpdate(Request $request)
    {
        $posteo = Posteo::find($request->post_id);
        $tipo_preg = ($posteo->tipo_ev=='calificada') ? 'selecciona' : 'texto' ;
        Pregunta::updateOrCreate(
            ['id' => $request->id],
            ['post_id' => $request->post_id, 'tipo_pregunta' =>  $tipo_preg ,
                'pregunta' => html_entity_decode($request->pregunta), 'rptas_json' => html_entity_decode($request->nuevasRptas),
                'rpta_ok' => $request->rpta_ok, 'estado' => $request->estado, 'ubicacion' => 'despues']
        );
        // Si tema no tiene EV (preguntas) entonces debe ser NO-EVALUABLE
        // $tema_preguntas = Pregunta::where('post_id', $posteo->curso_id)->where('estado', '1')->first();
        // if ($tema_preguntas) {
        //     $posteo->evaluable = 'si';
        //     $posteo->tipo_ev = 'calificada';
        // } else {
        //     $posteo->evaluable = 'no';
        //     $posteo->tipo_ev = '';
        // }
        // $posteo->save();

        ////// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $posteo->curso_id)->where('evaluable', 'si')->first();
        $curso = $posteo->curso;
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////

        return response()->json(['error' => false, 'msg' => 'Pregunta actualizada.'], 200);
    }

    public function update(Posteo $posteo, PreguntaStoreRequest $request, Pregunta $pregunta)
    {
        $pregunta->update($request->all());

        // Si tema no tiene EV (preguntas) entonces debe ser NO-EVALUABLE
        // $tema_preguntas = Pregunta::where('post_id', $posteo->id)->where('estado', '1')->first();
        // if ($tema_preguntas) {
        //     $posteo->evaluable = 'si';
        //     $posteo->tipo_ev = 'calificada';
        // } else {
        //     $posteo->evaluable = 'no';
        //     $posteo->tipo_ev = '';
        // }
        // $posteo->save();

        // ////// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $posteo->curso_id)->where('evaluable', 'si')->first();
        $curso = $posteo->curso;
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////

        return redirect()->route('posteos.preguntas', [$posteo->curso->id, $posteo->id])
            ->with('info', 'Registro actualizado con éxito');
    }


    public function destroy(Posteo $posteo, Pregunta $pregunta)
    {
        $pregunta->delete();

        // Si tema no tiene EV *(preguntas) entonces debe ser NO-EVALUABLE
        $tema_preguntas = Pregunta::where('post_id', $posteo->id)->where('estado', '1')->first();
        // if ($tema_preguntas) {
        //     $posteo->evaluable = 'si';
        //     $posteo->tipo_ev = 'calificada';
        // } else {
        //     $posteo->evaluable = 'no';
        //     $posteo->tipo_ev = '';
        // }
        // $posteo->save();

        //// Si el tema es evaluable, marcar CURSO también como evaluable
        $tema_evaluable = Posteo::where('curso_id', $posteo->curso_id)->where('evaluable', 'si')->first();
        $curso = $posteo->curso;
        if ($tema_evaluable) {
            $curso->c_evaluable = 'si';
        } else {
            $curso->c_evaluable = 'no';
        }
        $curso->save();
        //////

        return redirect()->route('posteos.preguntas', [$posteo->curso->id, $posteo->id])
            ->with('info', 'Eliminado correctamente');
    }

    public function subirExamen(Curso $curso, Posteo $posteo)
    {
        return view('preguntas.importar', compact('posteo', 'curso'));
    }

    public function guardarExamen(Curso $curso, Posteo $posteo, ExamenImportExcelRequest $request)
    {
        $data = $request->validated();
        $data['posteo_id'] = $posteo->id;
        $data['tipo_ev'] = $posteo->tipo_ev;

        $result = Pregunta::importFromFile($data);
        // $posteo->evaluable = 'si';
        // $posteo->tipo_ev = 'calificada';
        // $posteo->save();
        if ($result['status'] == 'error')
            return redirect()->back()->with('info', $result['message']);

        return redirect()->route('posteos.preguntas', [$posteo->curso->id, $posteo->id])
            ->with('info', $result['message']);
    }
}
