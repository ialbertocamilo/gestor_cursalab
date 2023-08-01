<?php

namespace App\Http\Controllers;

use App\Http\Requests\Speaker\SpeakerStoreUpdateRequest;
use Illuminate\Http\Request;

use App\Models\Speaker;
use App\Models\Media;
use App\Models\Poll;
use App\Models\Taxonomy;

class SpeakerController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace_id' => $workspace?->id]);
        $data = Speaker::getSpeakersList($request->all());

        return $this->success($data);
    }

    public function getData(Speaker $speaker)
    {
        $response = Speaker::getData($speaker);

        return $this->success($response);
    }

    public function store(SpeakerStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'image');

        $speaker = Speaker::storeRequest($data);

        $response = [
            'msg' => 'Speaker creado correctamente.',
            'speaker' => $speaker,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function update(SpeakerStoreUpdateRequest $request, Speaker $speaker)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'image');

        $speaker = Speaker::storeRequest($data, $speaker);

        $response = [
            'msg' => 'Speaker actualizado correctamente.',
            'speaker' => $speaker,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param Speaker $speaker
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Speaker $speaker, Request $request)
    {
        $speaker->update(['active' => !$speaker->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete speaker record
     *
     * @param Speaker $speaker
     * @return JsonResponse
     */
    public function destroy(Speaker $speaker)
    {
        $speaker->delete();

        return $this->success(['msg' => 'Speaker eliminado correctamente.']);
    }
}
