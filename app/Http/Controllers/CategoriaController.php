<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Ciclo;
use App\Models\Curso;
// use App\Perfil;
// use App\Curso_perfil;
// use App\Categoria_perfil;
use App\Models\Posteo;
use App\Models\Carrera;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Curricula;
use App\Models\Update_usuarios;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\CategoriaStoreRequest;
use App\Http\Controllers\ApiRest\HelperController;

class CategoriaController extends Controller
{

    public function cursos(Abconfig $abconfig, Categoria $categoria, Request $request)
    {
        if ($request->has('pid')) {
            // $ciclo_ids = Ciclo::select('id')->where('carrera_id', $request->input('pid'))->pluck('id');
            $cursospp = Curricula::select('curso_id')->where('carrera_id', $request->input('pid'))->pluck('curso_id');
            // dd($request->input('pid'));
            $cursos =  $categoria->cursos()->whereIn('id', $cursospp)->orderBy('orden')->get();
        }else{
            $cursos = Curso::with(['update_usuarios'=>function($q){$q->where(function ($query) {
                $query->where('estado', '=', 0)
                      ->orWhere('estado', '=', 1);
            })->select('estado','id','curso_id','created_at');},'curricula','curricula.carrera','curricula.ciclo'])->where('categoria_id',$categoria->id)->orderBy('orden')->get();
            //PARSE TIME
            foreach ($cursos as $curso) {
                if(count($curso->update_usuarios)>0){
                    $curso->parse_date = Carbon::parse($curso->update_usuarios->last()->created_at)->addMinutes(25)->format('h:i A');
                }
            }
        }
        // $curso_ids = $cursos->pluck('id');
        //BUSCAR CURSOS ELIMINADOS
        $c_eliminados = Update_usuarios::where('accion','curso_eliminado')->where(function ($query) {
            $query->where('estado', '=', 0)
                  ->orWhere('estado', '=', 1);
        })->where('categoria_id',$categoria->id)->select('curso_id','accion','estado','created_at','extra_info')->get();
        foreach ($c_eliminados as $el) {
            $el->parse_date = Carbon::parse($el->created_at)->addMinutes(25)->format('h:i A');
        }
        // $carreras = DB::table('carreras AS c')
        //                 ->select('c.id','c.nombre')
        //                 ->join('ciclos AS i','i.carrera_id','=','c.id')
        //                 ->join('curricula AS u','u.ciclo_id','=','i.id')
        //                 ->where('c.config_id', $abconfig->id)
        //                 //->whereIn('u.curso_id', $curso_ids)
        //                 ->where('c.estado', 1)
        //                 ->groupBy('c.id')
        //                 ->orderBy('c.nombre')
        //                 ->get();
        $carreras = DB::table('curricula AS cu')
                    ->join('carreras as c','c.id','cu.carrera_id')
                    ->where('c.config_id', $abconfig->id)
                    ->whereIn('cu.curso_id', $categoria->cursos()->select('id')->pluck('id'))
                    ->select('c.nombre','c.id')->groupBy('c.id')->get();
        $carreras_arr = $carreras->pluck('nombre','id');
        $ciclos_arr = Ciclo::select('id','nombre')->pluck('nombre','id' );
        // $carreras = Carrera::select('id','config_id','nombre','estado')->where('config_id', $abconfig->id)->orderBy('nombre')->get();
        // return $carreras;

        // return view('categorias.cursos', compact('categoria', 'cursos', 'perfiles'));
        return view('categorias.cursos', compact('categoria', 'cursos', 'carreras', 'carreras_arr', 'ciclos_arr','c_eliminados'));
    }

    // public function temas(Categoria $categoria)
    // {
    //     $posteos = $categoria->temas()->orderBy('orden')->paginate();
    //     return view('categorias.temas', compact('categoria','posteos'));
    // }


    // public function index(Request $request)
    // {
    //     if ($request->has('q')) {
    //         $question = $request->input('q');
    //         // return $question;
    //         $categorias = Categoria::where('nombre', 'like', '%'.$question.'%')->paginate();
    //     }else{
    //         $categorias = Categoria::paginate();
    //     }

    //     return view('categorias.index', compact('categorias'));
    // }

    public function create(Abconfig $abconfig)
    {
        // return $abconfig;
        $config_array = Abconfig::select('id','etapa')->where('id', $abconfig->id)->pluck('etapa','id' );
        // $perfiles = Perfil::get();
        return view('categorias.create', compact('config_array', 'abconfig'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Abconfig $abconfig, CategoriaStoreRequest $request)
    {
        // Mover imagen a carpea public/images
        // $image= $request->file('imagen');
        // $new_name = rand() . '.' . $image->getClientOriginalExtension();
        // $image->move(public_path('images'), $new_name);

         //cambiar valor de name en el request
        $data = $request->all();

        // if ($request->filled('imagen')) {
        //     $data['imagen'] = 'images/'.$request->imagen;
        // }
        if ($request->filled('imagen')) {
            $data['imagen'] = $request->imagen;
        }
        $data['reinicios_programado']=HelperController::getDataReinicioAutomatico($data);
        $categoria = Categoria::create($data);

        /**
         * GUARDAR NOMBRE CICLO 0
         */
        $this->guardarNombreCiclo0($categoria->id, $request->nombre_ciclo_0);

        //guardar perfiles
        // if ($request->has('perfiles')) {
        //     $perfiles = $request->perfiles;
        //     foreach ($perfiles as $key => $value) {
        //         $cur_perfiles = new Categoria_perfil;
        //         $cur_perfiles->categoria_id = $categoria->id;
        //         $cur_perfiles->perfil_id = $value;
        //         $cur_perfiles->save();
        //     }
        // }

        return redirect()->route('abconfigs.categorias', $abconfig->id)
                ->with('info', 'Registro guardado con éxito');
        // return redirect()->to( session('mod')['ruta'] )
                // ->with('info', 'Registro guardado con éxito');
    }

    public function guardarNombreCiclo0($categoria_id, $nombre)
    {
        if ( !empty($nombre) )
        {
            $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $categoria_id)->first();
            if ($nombre_ciclo_0) {
                $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $categoria_id)->update(['nombre'=> $nombre]);
            } else {
                $nombre_ciclo_0 = DB::table('nombre_escuelas')->insert([
                    'escuela_id' => $categoria_id,
                    'nombre' => $nombre,
                ]);
            }
        }
    }


    public function edit(Abconfig $abconfig, Categoria $categoria)
    {
        $config_array = Abconfig::select('id','etapa')->where('id', $abconfig->id)->pluck('etapa','id' );
        $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $categoria->id)->first();
        $categoria->nombre_ciclo_0 = (isset($nombre_ciclo_0->nombre) && $nombre_ciclo_0->nombre) ? $nombre_ciclo_0->nombre : '' ;
        // $perfiles = Perfil::get();

        if ($categoria->imagen != "") {
            $categoria->imagen = str_replace("images/", "", $categoria->imagen);
        }

        return view('categorias.edit', compact('categoria','config_array', 'abconfig'
            , 'nombre_ciclo_0'
        ));
    }

    public function update(Abconfig $abconfig, CategoriaStoreRequest $request, Categoria $categoria)
    {
        // return $request;
        $data = $request->all();

        // if ($request->has('imagen')) {
        //     // Mover imagen a carpea public/images
        //     $image= $request->file('imagen');
        //     $new_name = rand() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('images'), $new_name);

        //      //cambiar valor de name en el request
        //     $data['imagen'] = 'images/'.$new_name;

        //             //eliminar imagen anterior
        //     \File::delete(public_path().'/'.$categoria->imagen);
        // }

        // if ($request->filled('imagen')) {
        //     $data['imagen'] = 'images/'.$request->imagen;
        // }
        if ($request->filled('imagen')) {
            $data['imagen'] = $request->imagen;
        }
        $data['reinicios_programado']= HelperController::getDataReinicioAutomatico($data);
        $categoria->update($data);

        /**
         * GUARDAR NOMBRE CICLO 0
         */
        $this->guardarNombreCiclo0($categoria->id, $request->nombre_ciclo_0);



        // if ($request->has('perfiles')) {
        //     //eliminar anteriores datos de posteo perfil
        //     $categoria->cate_perfiles()->delete();
        //     //guardar perfiles
        //     $perfiles = $request->perfiles;
        //     foreach ($perfiles as $key => $value) {
        //         $cur_perfiles = new Categoria_perfil;
        //         $cur_perfiles->categoria_id = $categoria->id;
        //         $cur_perfiles->perfil_id = $value;
        //         $cur_perfiles->save();
        //     }
        // }

        return redirect()->route('abconfigs.categorias', $abconfig->id)
                ->with('info', 'Registro actualizado con éxito');
        // return redirect()->to( session('mod')['ruta'] )
                // ->with('info', 'Registro actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Categoria  $categoria
     * @return \Illuminate\Http\Response
     */
    public function destroy(Abconfig $abconfig, Categoria $categoria)
    {
        // \File::delete(public_path().'/'.$categoria->imagen);
        //eliminar anteriores
        // $categoria->cate_perfiles()->delete();

        DB::table('nombre_escuelas')->where('escuela_id', $categoria->id)->delete();
        $categoria->delete();

        return redirect()->route('abconfigs.categorias', $abconfig->id)->with('info', 'Eliminado correctamente');
    }
}
