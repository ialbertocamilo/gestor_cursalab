<?php

namespace App\Http\Controllers;

use App\Http\Requests\PollStoreRequest;
use App\Http\Resources\EncuestaResource;
use App\Models\Media;
use App\Models\Poll;
use App\Models\Taxonomy;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $encuestas = Poll::search($request);

        EncuestaResource::collection($encuestas);

        return $this->success($encuestas);
    }

    public function preguntas(Poll $poll)
    {
        $encuestas_preguntas = $poll->encuestas()->paginate();

        return view(
            'encuestas.preguntas',
            compact('poll','encuestas_preguntas')
        );
    }
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            // return $question;
            $encuestas = Poll::where('titulo', 'like', '%'.$question.'%')->paginate();
        }else{
        $encuestas = Poll::paginate();
       }
        return view('encuestas.index', compact('encuestas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create()
    {
        $secciones = Taxonomy::getDataForSelect('poll', 'tipo');
        $tipos = config('data.polls.tipos');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to store a newly created resource in storage.
     *
     * @param PollStoreRequest $request
     * @return JsonResponse
     */
    public function store(PollStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');

        $session = $request->session()->all();
        $workspace = $session['workspace'];
        $data['workspace_id'] = $workspace->id;

        $platform_training = Taxonomy::getFirstData('project', 'platform', 'training');
        $data['platform_id'] = $request['platform_id'] ?? $platform_training?->id;

        Poll::create($data);

        $msg = 'Encuesta creada correctamente.';

        return $this->success(compact('msg'));
    }

    /**
     * Process request to load data for edit form
     *
     * @param Poll $poll
     * @return JsonResponse
     */
    public function edit(Poll $poll)
    {

        $secciones = Taxonomy::getDataForSelect('poll', 'tipo');
        $tipos = config('data.polls.tipos');

        return $this->success(get_defined_vars());
    }

    /**
     * Process to update the specified resource in storage.
     *
     * @param PollStoreRequest $request
     * @param Poll $poll
     * @return JsonResponse
     */
    public function update(PollStoreRequest $request, Poll $poll)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');

        $poll->update($data);

        $msg = 'Encuesta actualizada correctamente.';

        return $this->success(compact('msg'));
    }

    /**
     * Toggle poll status between 1 or 0
     *
     * @param Poll $poll
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Poll $poll, Request $request)
    {
        $poll->update(['active' => !$poll->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Poll $poll
     * @return JsonResponse
     */
    public function destroy(Poll $poll)
    {

        if ($poll->countCoursesRelated() > 0) {
            return $this->error('Una encuesta asociada a uno o más cursos no puede eliminarse.');
        }

        $poll->delete();

        return $this->success(['msg' => 'Encuesta eliminada correctamente.']);
    }
}
