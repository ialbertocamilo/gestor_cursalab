<?php

namespace App\Http\Controllers;

use App\Http\Requests\Benefit\BenefitStoreUpdateRequest;
use Illuminate\Http\Request;

use App\Models\Benefit;
use App\Models\Course;
use App\Models\EmailSegment;
use App\Models\Media;
use App\Models\Poll;
use App\Models\Segment;
use App\Models\Speaker;
use App\Models\Taxonomy;
use App\Models\User;

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
        $data = $request->all();
        $benefit_id = $data['id'] ?? null;

        // Segmentación directa
        $list_segments_direct = !is_null($benefit_id) ? $this->listSegmentDirect($benefit_id, $data) : null;

        // Segmentación por documento
        $list_segments_document = !is_null($benefit_id) ? $this->listSegmentDocument($benefit_id, $data) : null;

        $users_assigned = $this->countUsersSegmentedBenefit($list_segments_direct, $list_segments_document);

        if(count($users_assigned) > 0)
        {
            $push_chunk = 50;
            $chunk = array_chunk($users_assigned, $push_chunk);

            if(count($chunk) > 1) {
                foreach ($chunk as $key => $agrupado) {
                    $email_segment = new EmailSegment();
                    $email_segment->workspace_id = get_current_workspace()?->id;
                    $email_segment->benefit_id = $benefit_id;
                    $email_segment->users = json_encode($agrupado);
                    $email_segment->chunk = $key + 1;
                    $email_segment->sent = false;
                    $email_segment->save();
                }
            }
            else {
                foreach ($chunk as $key => $agrupado) {
                    $email_segment = new EmailSegment();
                    $email_segment->workspace_id = get_current_workspace()?->id;
                    $email_segment->benefit_id = $benefit_id;
                    $email_segment->users = json_encode($agrupado);
                    $email_segment->chunk = $key + 1;
                    $email_segment->sent = true;
                    $email_segment->save();
                    if(is_array($agrupado)) {
                        foreach($agrupado as $user_id) {
                            $user = User::where('id', $user_id)->select('email')->first();
                            if($user) {
                                $benefit = Benefit::where('id', $benefit_id)->first();
                                if($benefit) {
                                    Benefit::sendEmail( 'new', $user, $benefit );
                                }
                            }
                        }
                    }
                }
            }
        }
        cache_clear_model(EmailSegment::class);

        // Segmentación directa
        if( !is_null($list_segments_direct) ) {
            (new Segment)->storeDirectSegmentation($list_segments_direct);
        }

        // Segmentación por documento
        if( !is_null($list_segments_document) ) {
            (new Segment)->storeSegmentationByDocumentForm($list_segments_document);
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

    public function usersSegmentedBenefit(Request $request)
    {
        $data = $request->all();
        $benefit_id = $data['id'] ?? null;

        // Segmentación directa
        $list_segments_direct = !is_null($benefit_id) ? $this->listSegmentDirect($benefit_id, $data) : null;

        // Segmentación por documento
        $list_segments_document = !is_null($benefit_id) ? $this->listSegmentDocument($benefit_id, $data) : null;

        $users_assigned = $this->countUsersSegmentedBenefit($list_segments_direct, $list_segments_document);

        $msg = 'Total de usuarios asignados';

        return $this->success(['msg' => $msg, 'benefit'=>$benefit_id, 'users'=> count($users_assigned)]);
    }


    private function countUsersSegmentedBenefit( $list_segments_direct = null, $list_segments_document = null) {

        $segments_direct = !is_null($list_segments_direct) ? (new Segment)->preDirectSegmentation($list_segments_direct) : [];
        $segments_document = !is_null($list_segments_document) ? (new Segment)->preSegmentationByDocument($list_segments_document) : [];

        $segments = array_merge($segments_direct,$segments_document);

        $users_segmented = new Course();
        $users_assigned = $users_segmented->usersSegmented($segments, $type = 'users_id');

        return $users_assigned;
    }

    private function listSegmentDirect( $benefit_id, $data ) {

        if(isset($data['list_segments']['segments']) && count($data['list_segments']['segments']) > 0)
        {
            $data['list_segments']['model_id'] = $benefit_id;

            $list_segments_temp = [];
            foreach($data['list_segments']['segments'] as $seg) {
                if($seg['type_code'] === 'direct-segmentation')
                    array_push($list_segments_temp, $seg);
            }
            $data['list_segments']['segments'] = $list_segments_temp;

            return (object) $data['list_segments'];
        }
        return null;
    }

    private function listSegmentDocument( $benefit_id, $data ) {

        if( isset($data['list_segments_document']['segment_by_document']) &&
            isset($data['list_segments_document']['segment_by_document']['segmentation_by_document']) &&
            is_array($data['list_segments_document']['segment_by_document']['segmentation_by_document']) &&
            count($data['list_segments_document']['segment_by_document']['segmentation_by_document']) > 0
        )
        {
            $data['list_segments_document']['model_id'] = $benefit_id;
            return $data['list_segments_document'];
        }
        return null;
    }

}
