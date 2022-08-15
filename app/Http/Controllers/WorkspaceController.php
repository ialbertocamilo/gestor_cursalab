<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{

    /**
     * Process request to render workspaces' selector view
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request) {

        return view(
            'workspaces.list',
            []
        );
    }

    /**
     * Process request to render workspaces' configuration view
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function configuration(Request $request) {

        return view(
            'workspaces.configuration',
            []
        );
    }

    /**
     * Process request
     *
     * @param Request $request
     * @param Workspace $workspace
     * @return Application|Factory|View
     */
    public function edit(Request $request, Workspace $workspace) {


        return view(
            'workspaces.edit',
            []
        );
    }
}
