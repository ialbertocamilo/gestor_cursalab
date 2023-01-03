<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ReportsController extends Controller
{

    public function registerGeneratedReport(Request $request) {

        $user = auth()->user();

        // get admin workspace

        $workspaceId = session('workspace')->id;

        // Register report

        GeneratedReport::create([
            'name' => $request->name,
            'download_url' => $request->downloadUrl,
            'admin_id' => $user->id,
            'workspace_id' => $workspaceId,
            'filters' => json_encode($request->filters)
        ]);

        return response()->json(['success' => true]);
    }
}
