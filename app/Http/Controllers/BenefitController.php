<?php

namespace App\Http\Controllers;

use App\Http\Requests\Benefit\BenefitStoreUpdateRequest;
use Illuminate\Http\Request;

use App\Models\Benefit;
use App\Models\Poll;
use App\Models\Speaker;
use App\Models\Taxonomy;

class BenefitController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace_id' => $workspace?->id]);
        $data = Benefit::getBenefitsList($request->all());

        return $this->success($data);
    }

    public function getData(Benefit $benefit)
    {
        $response = Benefit::getData($benefit);

        return $this->success($response);
    }

    public function getSpeakers()
    {
        $data = Speaker::getSpeakers();

        return $this->success($data);
    }

    public function getFormSelects(Benefit $benefit = null, $compactResponse = false)
    {
        $workspace = get_current_workspace();

        // Type
        $types_benefit = Taxonomy::getDataForSelect('benefit', 'benefit_type');

        // Polls
        $types_poll= Taxonomy::getFirstData('poll', 'tipo', 'benefit');

        $polls = Poll::select('id', 'titulo as name')
                ->where('workspace_id', $workspace->id)
                ->where('type_id', $types_poll?->id)
                ->where('active', true)
                ->get();

        $response = compact('polls', 'types_benefit');

        return $compactResponse ? $response : $this->success($response);
    }

    public function store(BenefitStoreUpdateRequest $request)
    {
        $data = $request->validated();
        // $data = Media::requestUploadFile($data, 'imagen');

        $benefit = Benefit::storeRequest($data);

        $response = [
            'msg' => 'Beneficio creado correctamente.',
            'benefit' => $benefit,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function update(BenefitStoreUpdateRequest $request, Benefit $benefit)
    {
        $data = $request->validated();
        // $data = Media::requestUploadFile($data, 'imagen');

        $benefit = Benefit::storeRequest($data, $benefit);

        $response = [
            'msg' => 'Beneficio actualizado correctamente.',
            'benefit' => $benefit,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param Benefit $benefit
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Benefit $benefit, Request $request)
    {
        $benefit->update(['active' => !$benefit->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete benefit record
     *
     * @param Benefit $benefit
     * @return JsonResponse
     */
    public function destroy(Benefit $benefit)
    {
        $benefit->delete();

        return $this->success(['msg' => 'Beneficio eliminado correctamente.']);
    }
}
