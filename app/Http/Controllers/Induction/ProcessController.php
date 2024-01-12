<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Induction\ProcessStoreUpdateRequest;
use App\Models\Process;
use Illuminate\Http\Request;

class ProcessController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace_id' => $workspace?->id]);
        $data = Process::getProcessesList($request->all());

        return $this->success($data);
    }

    public function storeInline(Request $request)
    {
        $data = [ 'title' => $request->title ];

        $process = Process::storeRequest($data);

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function store(ProcessStoreUpdateRequest $request)
    {
        // $data = [
        //     'title' => $request->title,
        //     'description' => $request->description,
        //     'limit_absences' => $request->limit_absences ?? false,
        //     'count_absences' => $request->count_absences ?? false,
        //     'absences' => $request->absences ?? null,
        // ];

        $process = Process::storeRequest($request->validated());

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function update(Process $process, ProcessStoreUpdateRequest $request)
    {
        dd($process, $request);
        // $data = [
        //     'title' => $request->title,
        //     'description' => $request->description,
        //     'limit_absences' => $request->limit_absences ?? false,
        //     'count_absences' => $request->count_absences ?? false,
        //     'absences' => $request->absences ?? null,
        // ];

        // $process = Process::storeRequest($request->validated());

        $response = [
            'msg' => 'Proceso creado correctamente.',
            'process' => $process,
            'messages' => ['list' => []]
        ];
        return $this->success($response);
    }

    public function getData(Process $process)
    {
        $response = Process::getData($process);

        return $this->success($response);
    }
    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param Process $process
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Process $process, Request $request)
    {
        $process->update(['active' => !$process->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete process record
     *
     * @param Process $process
     * @return JsonResponse
     */
    public function destroy(Process $process)
    {
        $process->delete();

        return $this->success(['msg' => 'Proceso eliminado correctamente.']);
    }
}
