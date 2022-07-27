<?php

namespace App\Http\Controllers;

use App\Models\AyudaApp;
use App\Models\Post;
use App\Models\SortingModel;
use App\Http\Resources\AyudaAppResource;
use App\Http\Requests\AyudaAppStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AyudaAppController extends Controller
{

    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {

        $posts = Post::search($request);

        AyudaAppResource::collection($posts);

        return $this->success($posts);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Post $post)
    {

        $post->delete();
        return $this->success(['msg' => 'Ayuda eliminada correctamente.']);
    }

    /**
     * Process request to load data for edit form
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function edit(Post $post)
    {
        $post->default_order = SortingModel::getLastItemOrderNumber(
            Post::class, [], 'position'
        );

        return $this->success(get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create()
    {
        $default_order = SortingModel::getNextItemOrderNumber(
            Post::class, [], 'position'
        );

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AyudaAppStoreRequest $request
     * @return JsonResponse
     */
    public function store(AyudaAppStoreRequest $request)
    {
        $data = $request->validated();
        $post = Post::create($data);
        SortingModel::reorderItems(
            $post, [], null, 'position'
        );

        return $this->success(['msg' => 'AyudaApp creada correctamente.']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AyudaAppStoreRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(AyudaAppStoreRequest $request, Post $post)
    {
        $data = $request->validated();

        $last_order = $post->position;

        $post->update($data);

        SortingModel::reorderItems($post, [], $last_order, 'position');

        return $this->success(['msg' => 'Ayuda actualizada correctamente.']);
    }








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
}
