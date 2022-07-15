<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Criterion;
use App\Models\Media;
use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Requests\AnuncioStoreRequest;
use App\Http\Resources\AnuncioResource;

class AnuncioController extends Controller
{
    public function search(Request $request)
    {
        $anuncios = Anuncio::search($request);

        AnuncioResource::collection($anuncios);

        return $this->success($anuncios);
    }

    public function getListSelects()
    {
        $modules = Criterion::getValuesForSelect('module');
        // $modules = Criterion::getValuesForSelect('module');

        return $this->success(get_defined_vars());
    }

    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            $anuncios = Anuncio::where('nombre', 'like', '%'.$question.'%')->orderBy('created_at','DESC')->paginate();
        }else{
            $anuncios = Anuncio::orderBy('created_at','DESC')->paginate();
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

    public function store(AnuncioStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'archivo');

        $data['config_id'] = json_encode($request->modules);

        $anuncio = Anuncio::create($data);

        return $this->success(['msg' => 'Anuncio creado correctamente.']);
    }

    public function edit(Anuncio $anuncio)
    {
        $config_ids = json_decode($anuncio->config_id, true);
        $anuncio->modules = Abconfig::getModulesForSelect($config_ids);

        $modules = Criterion::getValuesForSelect('module');
        $destinos = config('data.destinos');

        return $this->success(get_defined_vars());
    }

    public function update(Anuncio $anuncio, AnuncioStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFile($data, 'imagen');
        $data = Media::requestUploadFile($data, 'archivo');

        $data['config_id'] = json_encode($request->modules);

        $anuncio->update($data);

        return $this->success(['msg' => 'Anuncio actualizado correctamente.']);
    }

    public function status(Anuncio $anuncio, Request $request)
    {
        $anuncio->update(['estado' => !$anuncio->estado]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function destroy(Anuncio $anuncio)
    {
        $anuncio->delete();

        return $this->success(['msg' => 'Anuncio eliminado correctamente.']);
    }


    // Eliminar adjuntos
    public function del_attached_video(Anuncio $anuncio)
    {
        // \File::delete(public_path().'/'.$anuncio->video);
        $anuncio->video = NULL;
        $anuncio->save();

        return redirect()->back();
    }

    public function del_attached_archivo(Anuncio $anuncio)
    {
        // \File::delete(public_path().'/'.$anuncio->archivo);
        $anuncio->archivo = NULL;
        $anuncio->save();

        return redirect()->back();
    }

    public function setPublicationDates()
    {
        $anuncios = Anuncio::whereNull('publication_starts_at')->whereNull('publication_ends_at')->get();

        foreach ($anuncios AS $anuncio)
        {
            $anuncio->publication_starts_at = $anuncio->publish_date->format('Y-m-d');
            $anuncio->publication_ends_at = $anuncio->publication_starts_at->addMonths(2);

            $anuncio->update();
        }

        echo "Anuncios actualizados: {$anuncios->count()}";
    }
}
