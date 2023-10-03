<?php

namespace App\Http\Controllers;

use App\Http\Requests\Benefit\BenefitStoreUpdateRequest;
use App\Models\UserNotification;
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
        if(isset($data['list_segments']['segments']) &&
        count($data['list_segments']['segments']) > 0)
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
        if(isset($data['list_segments_document']['segment_by_document']) &&
        isset($data['list_segments_document']['segment_by_document']['segmentation_by_document']))
        {
            $data['list_segments_document']['model_id'] = $benefit_id;
            $list_segments = $data['list_segments_document'];

            (new Segment)->storeSegmentationByDocumentForm($list_segments);
        }

        $msg = 'Beneficio segmentado.';

        // Create notification for segmented users

        $suscritos = Benefit::getSuscritos($benefit_id);
        if (isset($suscritos['segmentados'])) {
            $usersIds = $suscritos['segmentados']->pluck('id')->toArray();
            $benefit = Benefit::find($benefit_id);
            UserNotification::createNotifications(
                $benefit->workspace_id,
                $usersIds,
                UserNotification::NEW_BENEFIFT,
                [
                    'benefitName' => $benefit->name
                ],
                "beneficio?beneficio=$benefit_id"
            );
        }



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

        // Tags default
        $tags = Benefit::tagsDefault();

        $response = compact('polls', 'types_benefit', 'group', 'tags');

        return $compactResponse ? $response : $this->success($response);
    }

    public function store(BenefitStoreUpdateRequest $request)
    {
        $data = $request->validated();
        $data = Media::requestUploadFile($data, 'image');
        $data = Media::requestUploadFile($data, 'promotor_imagen');

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
        $data = Media::requestUploadFile($data, 'promotor_imagen');

        $benefit = Benefit::storeRequest($data, $benefit);

        $response = [
            'msg' => 'Beneficio actualizado correctamente.',
            'benefit' => $benefit,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function maxBenefitsxUsers()
    {
        $response = Benefit::maxBenefitsxUsers();

        return $this->success($response);
    }

    public function updateMaxBenefitsxUsers(Request $request)
    {
        $data = $request->all();
        $value = $data['value'] ?? null;

        $update = Benefit::updateMaxBenefitsxUsers($value);

        $response = [
            'msg' => 'Max. Cant. actualizada correctamente.',
            'max_benefits' => $update,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function assignedSpeaker(Request $request)
    {
        $data = $request->all();
        $benefit_id = $data['benefit_id'] ?? null;
        $speaker_id = $data['speaker_id'] ?? null;

        Benefit::assignedSpeaker($benefit_id, $speaker_id);

        $response = [
            'msg' => 'Se asignó el expositor correctamente.',
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function getSuscritos(Request $request)
    {
        $data = $request->all();
        $benefit_id = $data['benefit_id'] ?? null;

        $data = Benefit::getSuscritos($benefit_id);

        return $this->success($data);
    }

    public function updateSuscritos(Request $request)
    {
        $data = $request->all();
        $benefit_id = $data['benefit_id'] ?? null;
        $seleccionados = $data['seleccionados'] ?? null;

        $update = Benefit::updateSuscritos($benefit_id, $seleccionados);

        $response = [
            'msg' => 'Información actualizada correctamente.',
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function sendEmailSegments(Request $request)
    {
        $data = $request->all();
        $benefit_id = $data['benefit_id'] ?? null;

        $users_assigned = [];

        if(!is_null($benefit_id))
        {
            $course = new Course();
            $benefit = Benefit::with(['segments'])->where('id', $benefit_id)->first();
            if($benefit)
            {
                $users_assigned = $course->usersSegmented($benefit?->segments, $type = 'users_id');
                $users_assigned = array_unique($users_assigned);
            }

        $new_users_assigned = [];
        $users_segmented = EmailSegment::where('benefit_id', $benefit_id)->pluck('users')->toArray();
        foreach($users_segmented as $us) {
            $decode = json_decode($us);
            if(is_array($decode))
                $new_users_assigned = array_merge($new_users_assigned, $decode);
        }
        if(count($users_assigned) > count($new_users_assigned))
            $users_assigned = array_diff($users_assigned, $new_users_assigned);
        else
            $users_assigned = array_diff($new_users_assigned, $users_assigned);

        if(count($users_assigned) > 0)
        {
            $push_chunk = 40;
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

        }
        $response = [
            'msg' => 'Se envió los correos a los usuarios segmentados',
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
        $benefit_id = $data['benefit_id'] ?? null;
        $users_assigned = [];

        if(!is_null($benefit_id))
        {
            $course = new Course();
            $benefit = Benefit::with(['segments'])->where('id', $benefit_id)->first();
            if($benefit)
            {
                $users_assigned = $course->usersSegmented($benefit?->segments, $type = 'users_id');
                $users_assigned = array_unique($users_assigned);
            }
        }
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
