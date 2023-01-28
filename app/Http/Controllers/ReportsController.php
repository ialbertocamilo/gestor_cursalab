<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneratedReportResource;
use App\Models\GeneratedReport;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ReportsController extends Controller
{

    public function loadReportsQueue(Request $request): \Illuminate\Http\JsonResponse
    {

        $user = auth()->user();

        // get admin workspace

        $workspaceId = session('workspace')->id;

        // Load workspace's reports queue

        $reports = GeneratedReport::query()
            ->with('admin.subworkspace')
            ->where([
                'admin_id' => $user->id,
                'workspace_id' => $workspaceId,
            ])
            ->orderBy('created_at', 'desc')
            ->paginate();

        GeneratedReportResource::collection($reports);

        return $this->success($reports);
    }
}
