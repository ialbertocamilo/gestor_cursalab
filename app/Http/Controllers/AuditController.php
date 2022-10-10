<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Audit;
use App\Models\Taxonomy;
use App\Models\User;

use App\Http\Resources\AuditResource;
use App\Http\Resources\AuditActivityResource;

class AuditController extends Controller
{

    public function search(Request $request)
    {
        $audits = Audit::search($request);

        AuditResource::collection($audits);

        return $this->success($audits);
    }

    public function loadDataForSelects()
    {
        $data['actions'] = Taxonomy::getSelectData('system', 'event');
        $data['models'] = Taxonomy::where('type', 'model')
            ->where('group', 'system')
            ->where('active', 1)
            ->whereNotNull('path')
            ->orderBy('name', 'ASC')
            ->select(['name', 'path'])
            ->get();

        return $this->success($data);
    }

    public function show(Audit $audit)
    {
        $audit->load('user', 'model', 'action_name', 'event_name');

        if ($audit->isBasicEvent()) {
            $model = $audit->extract();
            $model->loadDefaultRelationships();
        }

        $audit = new AuditResource($audit);

        return $this->success($audit);
    }

    public function audit(int $id)
    {
        $segment = request()->segment(2);

        $client = Taxonomy::where('group', 'system')
                                ->where('type', 'platform')
                                ->where('code', 'client')
                                ->firstOrFail();

        $taxonomy = Taxonomy::where('group', 'system')
                                ->where('type', 'model')
                                ->where('code', $segment)
                                ->where('parent_id', $client->id)
                                ->firstOrFail();

        $path = $taxonomy->name;

        $model = $path::findOrFail($id);

        $audits = $model->ledgers()
                        ->latest('id')
                        ->with('user', 'model', 'action_name', 'event_name')
                        ->paginate(12);

        AuditResource::collection($audits);

        return $this->success($audits);
    }

    public function lastActivity(Request $request)
    {
        $request->merge(['filters' => ['user' => auth()->user()->id], 'descending' => 'true', 'rowsPerPage' => 12]);

        // info($request->all());

        $audits = Audit::search($request);

        $date_audits = $audits->groupBy(function($item) {
                    return $item->created_at->format('Y-m-d');
                 });

        $data = [];

        foreach ($date_audits as $date => $audits)
        {
            $first = $audits->first();

            $data[$date]['label'] = $first->created_at->isoFormat('dddd[,] D [de] MMMM');

            foreach ($audits as $key => $audit)
            {
                $data[$date]['actions'][] = new AuditActivityResource($audit);
            }
        }

        return $this->success($data);

        // $request->merge(['view' => 'show']);

        // $audit = new AuditResource($audit);

    }
}
