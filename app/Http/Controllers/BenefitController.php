<?php

namespace App\Http\Controllers;

use App\Http\Requests\Benefit\BenefitStoreUpdateRequest;
use Illuminate\Http\Request;

use App\Models\Benefit;
use App\Models\Poll;
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

    public function getFormSelects(Benefit $benefit = null, $compactResponse = false)
    {
        $types_poll= Taxonomy::getFirstData('poll', 'tipo', 'benefit');

        $workspace = get_current_workspace();

        $polls = Poll::select('id', 'titulo as name')
                ->where('workspace_id', $workspace->id)
                ->where('type_id', $types_poll?->id)
                ->where('active', true)
                ->get();

        $response = compact('polls');

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
}
