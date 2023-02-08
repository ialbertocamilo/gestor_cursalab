<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneratedReportResource;
use App\Models\GeneratedReport;
use App\Models\Taxonomy;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{

    /**
     * Load workspace's reports queue from the last month
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadReportsQueue(Request $request): JsonResponse
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
            ->where('created_at', '>=', Carbon::now()->firstOfMonth()->toDateTimeString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        // Load report types taxonomies

        $reportsTypes = Taxonomy::getDataByGroupAndType(
            'reports', 'report'
        );

        // Format reports data

        foreach ($reports as &$report) {

            $reportType = $reportsTypes->where('code', $report['report_type'])->first();
            $report['report_type'] = $reportType ? $reportType->name : '';
            $report['filters_descriptions'] = json_decode($report['filters_descriptions']) ?: new \stdClass();
            $report['created_at'] = date('d/m/Y g:i a', strtotime($report['created_at']));
            $report['updated_at'] = $report['updated_at'] ? date('d/m/Y g:i a', strtotime($report['updated_at'])) : '';
        }

        return $this->success($reports);
    }

    /**
     * Load report types from taxonomy
     * @return JsonResponse
     */
    public function loadRepotsTypes() {
        return $this->success(Taxonomy::getDataByGroupAndType(
            'reports', 'report'
        ));
    }
}
