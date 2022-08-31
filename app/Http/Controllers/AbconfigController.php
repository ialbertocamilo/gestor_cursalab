<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Requests\AbconfigStoreRequest;
use App\Http\Requests\Modulo\ModuloStoreUpdateRequest;
use App\Http\Resources\EscuelaResource;
use App\Http\Resources\ModuloResource;
use App\Models\Abconfig;
use App\Models\Categoria;
use App\Models\Categoria_perfil;
use App\Models\Criterio;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Models\Workspace;
use Illuminate\Http\Request;

class AbconfigController extends Controller
{

    public function search(Request $request)
    {
//        $modules = Abconfig::search($request);
        $request->merge(['code' => 'module']);
        $modules = CriterionValue::search($request);

        ModuloResource::collection($modules);

        return $this->success($modules);
    }

//    public function searchModulo(Abconfig $abconfig)
    public function searchModulo(CriterionValue $module)
    {
        $workspace = Workspace::where('criterion_value_id', $module->id)->first();

        // $reinicio_automatico = json_decode($workspace->reinicios_programado);
        $module->reinicio_automatico = $reinicio_automatico['activado'] ?? false;
        $module->reinicio_automatico_dias = $reinicio_automatico['reinicio_dias'] ?? 0;
        $module->reinicio_automatico_horas = $reinicio_automatico['reinicio_horas'] ?? 0;
        $module->reinicio_automatico_minutos = $reinicio_automatico['reinicio_minutos'] ?? 0;


        $workspace->load('main_menu');
        $workspace->load('side_menu');

        $formSelects = $this->getFormSelects(true);

        $formSelects['main_menu']->each(function ($item) use ($workspace) {
            $item->active = $workspace->main_menu->where('id', $item->id)->first() !== NULL;
        });

        $formSelects['side_menu']->each(function ($item) use ($workspace) {
            $item->active = $workspace->side_menu->where('id', $item->id)->first() !== NULL;
        });

        // $evaluacion = json_decode($workspace->mod_evaluaciones);
        $module->preg_x_ev = $evaluacion->preg_x_ev ?? NULL;
        $module->nota_aprobatoria = $evaluacion->nota_aprobatoria ?? NULL;
        $module->nro_intentos = $evaluacion->nro_intentos ?? NULL;

        return $this->success([
            'modulo' => $module,
            'main_menu' => $formSelects['main_menu'],
            'side_menu' => $formSelects['side_menu'],
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $main_menu = Taxonomy::group('system')->type('main_menu')
                            ->select('id', 'name')
                            ->get();
        $main_menu->each(function ($item) {
            $item->active = false;
        });

        $side_menu = Taxonomy::group('system')->type('side_menu')
                            ->select('id', 'name')
                            ->get();
        $side_menu->each(function ($item) {
            $item->active = false;
        });

        $response = compact('main_menu', 'side_menu');

        return $compactResponse ? $response : $this->success($response);
    }

    public function getSelects()
    {
        $modules = Abconfig::select('id', 'etapa as nombre')->get();

        return $this->success(compact('modules'));
    }

    public function usuarios(Abconfig $abconfig)
    {
        $usuarios = $abconfig->usuarios()->paginate();
        // return $usuarios;

        return view('abconfigs.usuarios', compact('abconfig','usuarios'));
    }

//     public function categorias(Abconfig $abconfig, Request $request)
//     {

//         $query = $abconfig->categorias()->withCount('cursos');

//         if ($request->has('pid')) {
//             $catespp = Categoria_perfil::select('categoria_id')->where('perfil_id', $request->input('pid'))->pluck('categoria_id');

//             $query->whereIn('id', $catespp);
//         }

//         if ($request->q)
//             $query->where('nombre', 'like', "%$request->q%");

//         $escuelas = $query->orderBy('orden')->paginate($request->paginate);

//         EscuelaResource::collection($escuelas);

// //        if ($request->isJson()) {
//         if ($request->has('page')) {
//             return $this->success($escuelas);
//         } else {
//             $categorias = $escuelas;
//             return view('abconfigs.categorias', compact('abconfig', 'categorias'));
//         }

//     }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(Request $request)
    // {
    //     if ($request->has('q')) {
    //         $question = $request->input('q');
    //         // return $question;
    //         $abconfigs = Categoria::where('etapa', 'like', '%'.$question.'%')->get();
    //     }else{
    //         $abconfigs = Abconfig::paginate();
    //     }

    //     return view('abconfigs.index', compact('abconfigs'));
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $requisito_array = Abconfig::select('id','etapa')->pluck('etapa','id' );
        $requisito_array->put('', 'Ninguno');
        return view('abconfigs.create', compact('requisito_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
//     public function store(AbconfigStoreRequest $request)
// //    public function store(ModuloStoreUpdateRequest $request)
//     {
//         $data = $request->all();
//         // if ($request->has('logo')) {
//         // // Mover imagen a carpea public/images
//         //     $logo= $request->file('logo');
//         //     $new_name = rand() . '.' . $logo->getClientOriginalExtension();
//         //     $logo->move(public_path('images'), $new_name);

//         //      //cambiar valor de name en el request
//         //     $data['logo'] = 'images/'.$new_name;
//         // }
//         //  if ($request->has('plantilla_diploma')) {
//         // // Mover imagen a carpea public/images
//         //     $plantilla_diploma= $request->file('plantilla_diploma');
//         //     $new_name = rand() . '.' . $plantilla_diploma->getClientOriginalExtension();
//         //     $plantilla_diploma->move(public_path('images'), $new_name);

//         //      //cambiar valor de name en el request
//         //     $data['plantilla_diploma'] = 'images/'.$new_name;
//         // }
//         if ($request->filled('logo')) {
//             $data['logo'] = 'images/'.$request->logo;
//         }
//         if ($request->filled('plantilla_diploma')) {
//             $data['plantilla_diploma'] = 'images/'.$request->plantilla_diploma;
//         }
//         $abconfig = Abconfig::create($data);

//         // return redirect()->route('abconfigs.edit', $abconfig->id)
//         //         ->with('info', 'Abconfig guardado con éxito');
//         return redirect()->route('abconfigs.index')
//             ->with('info', 'Configuracion guardada con éxito');
//     }

    public function edit(Abconfig $abconfig)
    {
        $requisito_array = Abconfig::select('id','etapa')->pluck('etapa','id' );
        $requisito_array->pull($abconfig->id);
        $requisito_array->put('', 'Ninguno');

        if ($abconfig->logo != "") {
            $abconfig->logo = str_replace("images/", "", $abconfig->logo);
        }

        if ($abconfig->plantilla_diploma != "") {
            $abconfig->plantilla_diploma = str_replace("images/", "", $abconfig->plantilla_diploma);
        }

        return view('abconfigs.edit', compact('abconfig','requisito_array'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Abconfig  $abconfig
     * @return \Illuminate\Http\Response
     */
//     public function update(AbconfigStoreRequest $request, Abconfig $abconfig)
// //    public function update(ModuloStoreUpdateRequest $request, Abconfig $abconfig)
//     {
//         $data = $request->all();

//         // if ($request->has('logo')) {
//         //     // Mover imagen a carpea public/images
//         //     $logo= $request->file('logo');
//         //     $new_name = rand() . '.' . $logo->getClientOriginalExtension();
//         //     $logo->move(public_path('images'), $new_name);

//         //      //cambiar valor de name en el request
//         //     $data['logo'] = 'images/'.$new_name;

//         //             //eliminar imagen anterior
//         //     \File::delete(public_path().'/'.$abconfig->logo);
//         // }
//         // if ($request->has('plantilla_diploma')) {
//         //     // Mover imagen a carpea public/images
//         //     $plantilla_diploma= $request->file('plantilla_diploma');
//         //     $new_name = rand() . '.' . $plantilla_diploma->getClientOriginalExtension();
//         //     $plantilla_diploma->move(public_path('images'), $new_name);

//         //      //cambiar valor de name en el request
//         //     $data['plantilla_diploma'] = 'images/'.$new_name;

//         //             //eliminar imagen anterior
//         //     \File::delete(public_path().'/'.$abconfig->plantilla_diploma);
//         // }

//         if ($request->filled('logo')) {
//             $data['logo'] = 'images/'.$request->logo;
//         }
//         if ($request->filled('plantilla_diploma')) {
//             $data['plantilla_diploma'] = 'images/'.$request->plantilla_diploma;
//         }
//         $data['reinicios_programado']= HelperController::getDataReinicioAutomatico($data);
//         $abconfig->update($data);

//         // return redirect()->route('abconfigs.edit', $abconfig->id)
//         //         ->with('info', 'Abconfigo actualizado con éxito');
//         return redirect()->route('abconfigs.index')
//             ->with('info', 'Configuracion guardada con éxito');
//     }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Abconfig  $abconfig
     * @return \Illuminate\Http\Response
     */
//     public function destroy(Abconfig $abconfig)
//     {
//         // \File::delete(public_path().'/'.$abconfig->plantilla_diploma);
//         $abconfig->delete();

//         return back()->with('info', 'Eliminado Correctamente');
//     }

//     public function storeModulo(ModuloStoreUpdateRequest $request)
//     {
//         $data = $request->validated();
//         $data = Media::requestUploadFile($data, 'logo');
//         $data = Media::requestUploadFile($data, 'plantilla_diploma');
// //        dd($data);
//         $modulo = Abconfig::storeRequest($data);
//         $msg = 'Módulo registrado correctamente.';
//         return $this->success(compact('modulo', 'msg'));
//     }

//     public function updateModulo(ModuloStoreUpdateRequest $request, Abconfig $modulo)
//     {
//         $data = $request->validated();
//         $data = Media::requestUploadFile($data, 'logo');
//         $data = Media::requestUploadFile($data, 'plantilla_diploma');
// //        dd($data);
// //        return $data;
//         $modulo = Abconfig::storeRequest($data, $modulo);
//         $msg = 'Módulo actualizado correctamente.';
//         return $this->success(compact('modulo', 'msg'));
//     }
}
