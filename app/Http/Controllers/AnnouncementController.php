<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementStoreRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Criterion;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function search(Request $request)
    {
        $anuncios = Announcement::search($request);

        AnnouncementResource::collection($anuncios);

        return $this->success($anuncios);
    }

    public function getListSelects()
    {
        $modules = Criterion::getValuesForSelect('module');

        return $this->success(get_defined_vars());
    }

    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            $anuncios = Announcement::where('nombre', 'like', '%'.$question.'%')->orderBy('created_at','DESC')->paginate();
        }else{
            $anuncios = Announcement::orderBy('created_at','DESC')->paginate();
        }

        return view('anuncios.index', compact('anuncios'));
    }

    public function create()
    {
        $modules = Criterion::getValuesForSelect('module');
        $destinos = config('data.destinos');

        return $this->success(get_defined_vars());
    }

    public function getFormSelects()
    {
        $modules = Criterion::getValuesForSelect('module');

        return $this->success(get_defined_vars());
    }

    /**
     * Create new announcement
     *
     * @param AnnouncementStoreRequest $request
     * @return JsonResponse
     */
    public function store(AnnouncementStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'archivo');

        $data['config_id'] = json_encode($request->modules);

        $anuncio = Announcement::create($data);

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
        $config_ids = json_decode($announcement->config_id, true);
        $announcement->modules = Abconfig::getModulesForSelect($config_ids);

        $modules = Abconfig::getModulesForSelect();
        $destinos = config('data.destinos');

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

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'archivo');

        $data['config_id'] = json_encode($request->modules);

        $announcement->update($data);

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
