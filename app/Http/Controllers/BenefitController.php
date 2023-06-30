<?php

namespace App\Http\Controllers;

use App\Http\Requests\Benefit\BenefitStoreUpdateRequest;
use Illuminate\Http\Request;

use App\Models\Benefit;
use App\Models\Media;
use App\Models\Poll;
use App\Models\Segment;
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

    public function getSegments(Benefit $benefit)
    {
        $response = Benefit::getSegments($benefit);

        return $this->success($response);
    }

    public function saveSegment(Request $request)
    {
        $workspace = get_current_workspace();
        $data = $request->all();
        $benefit_id = $data['id'] ?? null;

        // Segmentación directa
        if(isset($data['list_segments']['segments']) && count($data['list_segments']['segments']) > 0)
        {
            $data['list_segments']['model_id'] = $benefit_id;

            $list_segments_temp = [];
            foreach($data['list_segments']['segments'] as $seg) {
                if($seg['type_code'] === 'direct-segmentation')
                    array_push($list_segments_temp, $seg);
            }
            $data['list_segments']['segments'] = $list_segments_temp;

            $list_segments = (object) $data['list_segments'];

            (new Segment)->storeDirectSegmentation($list_segments);
        }
        // Segmentación por documento
        if(isset($data['list_segments_document']['segment_by_document']) && isset($data['list_segments_document']['segment_by_document']['segmentation_by_document']))
        {
            $data['list_segments_document']['model_id'] = $benefit_id;
            $list_segments = $data['list_segments_document'];

            (new Segment)->storeSegmentationByDocumentForm($list_segments);
        }

        $msg = 'Beneficio segmentado.';

        return $this->success(['msg' => $msg, 'benefit'=>$benefit_id]);
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

        // Groups
        $group= Taxonomy::getDataForSelect('benefit', 'group');

        $response = compact('polls', 'types_benefit', 'group');

        return $compactResponse ? $response : $this->success($response);
    }

    public function store(BenefitStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'image');

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
        $data = Media::requestUploadFile($data, 'image');

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
