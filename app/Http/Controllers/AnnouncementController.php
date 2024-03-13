<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementStoreRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Media;
use App\Models\WorkspaceFunctionality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $workspace = get_current_workspace();

        $request->mergeIfMissing(['workspace_id' => $workspace->id]);

        $anuncios = Announcement::search($request);

        AnnouncementResource::collection($anuncios);

        return $this->success($anuncios);
    }

    /**
     * Get items list for select inputs
     *
     * @return JsonResponse
     */
    public function getListSelects()
    {
        $modules = Criterion::getValuesForSelect('module', true);
        // $modules = Criterion::getValuesForSelect('module');

        return $this->success(get_defined_vars());
    }

    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            $anuncios = Announcement::where('nombre', 'like', '%'.$question.'%')->orderBy('created_at','DESC')->paginate();
        } else {
            $anuncios = Announcement::orderBy('created_at','DESC')->paginate();
        }

        return view('anuncios.index', compact('anuncios'));
    }

    public function create()
    {
        $modules = Criterion::getValuesForSelect('module', true);
        $destinos = config('data.destinos');

        $functionalities = WorkspaceFunctionality::functionalities( get_current_workspace()->id );
        if(in_array('benefits', $functionalities)) {
            if(is_array($destinos)) {
                array_push($destinos, array('id'=> 'beneficios', 'nombre'=>'Beneficios'));
            }
        }

        return $this->success(get_defined_vars());
    }

    public function getFormSelects()
    {
        $modules = Criterion::getValuesForSelect('module');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to create new announcement
     *
     * @param AnnouncementStoreRequest $request
     * @return JsonResponse
     */
    public function store(AnnouncementStoreRequest $request)
    {
        $data = $request->validated();

        // Validate storage limit

        $files = [];
        if (isset($data['file_imagen'])) $files[] = $data['file_imagen'];
        if (isset($data['file_archivo'])) $files[] = $data['file_archivo'];
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);

        if (!$hasStorageAvailable) {
            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }

        // Save announcement

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'archivo');
        $modulesIds = $data['module_ids'];
        unset($data['module_ids']);

        // Create announcement
        $announcement = Announcement::create($data);

        // Save relationships
        $announcement->criterionValues()->sync($modulesIds);

        // Register notifications
        Announcement::registerNotificationsForAnnouncement($announcement->id);

        return $this->success(['msg' => 'Anuncio creado correctamente.']);
    }

    /**
     * Process request to return announcement values
     *
     * @param Announcement $announcement
     * @return JsonResponse
     */
    public function edit(Announcement $announcement)
    {
        $announcement['module_ids'] = $announcement->criterionValues()
            ->pluck('criterion_value_id');
        $modules = Criterion::getValuesForSelect('module', true);
        $destinos = config('data.destinos');

        $functionalities = WorkspaceFunctionality::functionalities( get_current_workspace()->id );
        if(in_array('benefits', $functionalities)) {
            if(is_array($destinos)) {
                array_push($destinos, array('id' => 'beneficios', 'nombre' => 'Beneficios'));
            }
        }

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to update announcement
     *
     * @param Announcement $announcement
     * @param AnnouncementStoreRequest $request
     * @return JsonResponse
     */
    public function update(Announcement $announcement, AnnouncementStoreRequest $request)
    {
        $data = $request->validated();

        // Validate storage limit

        $files = [];
        if (isset($data['file_imagen'])) $files[] = $data['file_imagen'];
        if (isset($data['file_archivo'])) $files[] = $data['file_archivo'];
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);

        if (!$hasStorageAvailable) {
            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }

        // Save announcement

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'archivo');

        $modulesIds = $data['module_ids'];
        unset($data['module_ids']);

        // Update announcement

        $announcement->update($data);

        // Save relationships

        $announcement->criterionValues()->sync($modulesIds);

        cache_clear_model(CriterionValue::class);

        return $this->success(['msg' => 'Anuncio actualizado correctamente.']);
    }

    /**
     * Process request to toggle value of active status (1 or 0)
     *
     * @param Announcement $announcement
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Announcement $announcement, Request $request)
    {
        $announcement->update(['active' => !$announcement->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete announcement record
     *
     * @param Announcement $announcement
     * @return JsonResponse
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return $this->success(['msg' => 'Anuncio eliminado correctamente.']);
    }


    // Eliminar adjuntos
    public function del_attached_video(Announcement $anuncio)
    {
        // \File::delete(public_path().'/'.$anuncio->video);
        $anuncio->video = NULL;
        $anuncio->save();

        return redirect()->back();
    }

    public function del_attached_archivo(Announcement $anuncio)
    {
        // \File::delete(public_path().'/'.$anuncio->archivo);
        $anuncio->archivo = NULL;
        $anuncio->save();

        return redirect()->back();
    }

    public function setPublicationDates()
    {
        $anuncios = Announcement::whereNull('publication_starts_at')->whereNull('publication_ends_at')->get();

        foreach ($anuncios AS $anuncio)
        {
            $anuncio->publication_starts_at = $anuncio->publish_date->format('Y-m-d');
            $anuncio->publication_ends_at = $anuncio->publication_starts_at->addMonths(2);

            $anuncio->update();
        }

        echo "Anuncios actualizados: {$anuncios->count()}";
    }
}
