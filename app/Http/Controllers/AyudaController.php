<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Media;
use App\Models\SortingModel;

use App\Http\Requests\AyudaStoreRequest;
use App\Http\Resources\AyudaResource;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AyudaController extends Controller
{
    public function search(Request $request)
    {
        $ayudas = Ayuda::search($request);

        AyudaResource::collection($ayudas);

        return $this->success($ayudas);
    }

    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index(Request $request)
    {
        // if ($request->has('q')) {
        //     $question = $request->input('q');
        //     // return $question;
        //     $ayudas = Ayuda::where('name', 'like', '%'.$question.'%')->paginate();
        // }else{
            // $ayudas = Ayuda::orderBy('orden')->paginate();
        // }
        // return view('ayudas.index', compact('ayudas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $default_order = SortingModel::getNextItemOrderNumber(Ayuda::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AyudaStoreRequest $request
     * @return Response
     */
    public function store(AyudaStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');

        $ayuda = Ayuda::create($data);

        SortingModel::reorderItems($ayuda);

        return $this->success(['msg' => 'Ayuda creada correctamente.']);
    }

    public function edit(Ayuda $ayuda)
    {
        $ayuda->default_order = SortingModel::getLastItemOrderNumber(Ayuda::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ayuda  $ayuda
     * @return Response
     */
    public function update(AyudaStoreRequest $request, Ayuda $ayuda)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');

        $last_order = $ayuda->orden;

        $ayuda->update($data);

        SortingModel::reorderItems($ayuda, [], $last_order);

        return $this->success(['msg' => 'Ayuda actualizada correctamente.']);
    }

    public function status(Ayuda $ayuda, Request $request)
    {
        $ayuda->update(['estado' => !$ayuda->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ayuda  $ayuda
     * @return Response
     */
    public function destroy(Ayuda $ayuda)
    {
        // \File::delete(public_path().'/'.$ayuda->imagen);
        $ayuda->delete();

        return $this->success(['msg' => 'Ayuda eliminada correctamente.']);
    }
}
