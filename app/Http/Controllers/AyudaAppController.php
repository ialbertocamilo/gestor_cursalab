<?php

namespace App\Http\Controllers;

use App\Models\AyudaApp;
use App\Models\SortingModel;
use App\Http\Resources\AyudaAppResource;
use App\Http\Requests\AyudaAppStoreRequest;
use Illuminate\Http\Request;

class AyudaAppController extends Controller
{

    public function getData(){
        $lista_ayuda = AyudaApp::select('id','nombre','orden','check_text_area')->orderBy('orden')->get();
        return response()->json(compact('lista_ayuda'));
    }

    public function saveData(Request $request){
        $data_update_create = collect($request->get('update_create'));
        $data_delete = collect($request->get('delete'));

        $data_update_create->each(function ($item){
            AyudaApp::updateOrCreate(
                ['id' => $item['id']],
                $item
            );
        });
        AyudaApp::whereIn('id',$data_delete)->delete();
        return response()->json(['result'=>'ok']);
    }



    public function search(Request $request)
    {
        $ayudas = AyudaApp::search($request);

        AyudaAppResource::collection($ayudas);

        return $this->success($ayudas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default_order = SortingModel::getNextItemOrderNumber(AyudaApp::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AyudaAppStoreRequest $request)
    {
        $data = $request->validated();

        $ayuda = AyudaApp::create($data);

        SortingModel::reorderItems($ayuda);

        return $this->success(['msg' => 'AyudaApp creada correctamente.']);
    }

    public function edit(AyudaApp $ayuda_app)
    {
        $ayuda_app->default_order = SortingModel::getLastItemOrderNumber(AyudaApp::class);

        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AyudaApp  $ayuda_app
     * @return \Illuminate\Http\Response
     */
    public function update(AyudaAppStoreRequest $request, AyudaApp $ayuda_app)
    {
        $data = $request->validated();
    
        $last_order = $ayuda_app->orden;
        
        $ayuda_app->update($data);

        SortingModel::reorderItems($ayuda_app, [], $last_order);

        return $this->success(['msg' => 'Ayuda actualizada correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AyudaApp  $ayuda
     * @return \Illuminate\Http\Response
     */
    public function destroy(AyudaApp $ayuda_app)
    {
        // \File::delete(public_path().'/'.$ayuda_app->imagen);
        $ayuda_app->delete();

        return $this->success(['msg' => 'Ayuda eliminada correctamente.']);
    }

}
