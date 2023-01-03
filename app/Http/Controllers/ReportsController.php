<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use Illuminate\Http\Request;

class ReportsController extends Controller
{

    public function registerGeneratedReport(Request $request) {

        $user = auth()->user();

        $generatedReport = new GeneratedReport();
        $generatedReport->name = $request->name;
        $generatedReport->admin_id = $user->id;
        $generatedReport->filters = json_encode($request->filters);
        $generatedReport->save();

        return response()->json(['success' => true]);
    }
}
