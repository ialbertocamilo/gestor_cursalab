<?php

namespace App\Http\Controllers;

use App\Http\Requests\PollQuestionStoreRequest;
use App\Http\Resources\PollQuestionResource;
use App\Models\Poll;
use App\Models\PollQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PollQuestionController extends Controller
{

    /**
     * Search poll question
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $questions = PollQuestion::search($request);

        PollQuestionResource::collection($questions);

        return $this->success($questions);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // if ($request->has('q')) {
        //     $question = $request->input('q');
        //     // return $question;
        //     $encuestas_preguntas = Encuestas_pregunta::where('titulo', 'like', '%'.$question.'%')->paginate();
        // }else{
        //     $encuestas_preguntas = Encuestas_pregunta::paginate();
        // }
        // return view('encuestas_preguntas.index', compact('encuestas_preguntas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create(Poll $encuesta)
    {
        // $encuesta_array = Encuesta::select('id','titulo')->pluck('titulo','id' );
        // return view('encuestas_preguntas.create', compact('encuesta_array'));

        $tipos = config('data.tipo-preguntas');

        return $this->success(get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Poll $poll
     * @param PollQuestionStoreRequest $request
     * @return JsonResponse
     */
    public function store(Poll $poll, PollQuestionStoreRequest $request)
    {
        $data = $request->validated();
        $data['poll_id'] = $poll->id;
        $data['opciones'] = PollQuestion::setOptionsToStore($request->opciones ?? []);

        $encuestas_pregunta = PollQuestion::create($data);

        return $this->success(['msg' => 'Pregunta creada correctamente.']);
    }

    /**
     * Update an existing poll question
     *
     * @param Poll $poll
     * @param PollQuestion $pollquestion
     * @return JsonResponse
     */
    public function edit(Poll $poll, PollQuestion $pollquestion)
    {
        $tipos = config('data.tipo-preguntas');

        $key = array_search(
            $pollquestion->tipo_pregunta, array_column($tipos, 'id')
        );

        $pollquestion->tipo_pregunta = $tipos[$key];
        $pollquestion->opciones = $pollquestion->formatOptions();

        return $this->success(get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Poll $poll
     * @param PollQuestion $pollquestion
     * @param PollQuestionStoreRequest $request
     * @return JsonResponse
     */
    public function update(Poll $poll, PollQuestion $pollquestion, PollQuestionStoreRequest $request)
    {
        $data = $request->validated();
        $data['opciones'] = PollQuestion::setOptionsToStore(
            $request->opciones ?? []
        );

        $pollquestion->update($data);

        return $this->success(['msg' => 'Pregunta actualizada correctamente.']);
    }

    /**
     * Toggle pollQuestion status between 1 or 0
     *
     * @param Poll $poll
     * @param PollQuestion $pollquestion
     * @return JsonResponse
     */
    public function status(Poll $poll, PollQuestion $pollquestion)
    {

        $pollquestion->update(['active' => !$pollquestion->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Poll $poll
     * @param PollQuestion $pollquestion
     * @return JsonResponse
     */
    public function destroy(Poll $poll, PollQuestion $pollquestion)
    {
        $pollquestion->delete();

        return $this->success(['msg' => 'Pregunta eliminada correctamente.']);
    }
}
