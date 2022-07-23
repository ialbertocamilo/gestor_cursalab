<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqsStoreRequest;
use App\Http\Resources\FaqsResource;
use App\Models\Post;
use App\Models\Pregunta_frecuente;
use App\Models\SortingModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqsController extends Controller
{
    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $faqs = Post::search($request);

        FaqsResource::collection($faqs);

        return $this->success($faqs);
    }

    /**
     * Process request to load data for create form
     *
     * @return JsonResponse
     */
    public function create()
    {
        // Generate position number for the next item

        $default_order = SortingModel::getNextItemOrderNumber(
            Post::class, [], 'position'
        );

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to store a record
     *
     * @param FaqsStoreRequest $request
     * @return JsonResponse
     */
    public function store(FaqsStoreRequest $request)
    {
        $faq = Post::create($request->validated());

        SortingModel::reorderItems(
            $faq, [], null, 'position'
        );

        return $this->success(['msg' => 'Pregunta frecuente creada correctamente.']);
    }

    /**
     * Process request to toggle poll status between 1 or 0
     *
     * @param Post $post
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Post $post, Request $request)
    {
        $post->update(['active' => !$post->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete record
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return $this->success(['msg' => 'Pregunta frecuente eliminada correctamente.']);
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
     * Process request to update the specified record
     *
     * @param FaqsStoreRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(FaqsStoreRequest $request, Post $post)
    {
        $last_order = $post->position;

        $post->update($request->validated());

        SortingModel::reorderItems($post, [], null, 'position');

        return $this->success(['msg' => 'Pregunta frecuente actualizada correctamente.']);
    }



}
