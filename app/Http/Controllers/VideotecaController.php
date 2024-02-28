<?php

namespace App\Http\Controllers;

use App\Http\Requests\Videoteca\VideotecaStoreRequest;
use App\Http\Resources\VideotecaResource;
use App\Models\Abconfig;
use App\Models\Criterion;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Models\Usuario;
use App\Models\Workspace;
use App\Models\UsuarioAccion;
use App\Models\Videoteca;
use Faker\Factory as Faker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class VideotecaController extends Controller
{

    // todo: check usage
    public function fakeData(Request $request)
    {
        $faker = Faker::create('es_ES');

        $tags = config('constantes.videoteca-test-tags');
        $categories = config('constantes.videoteca-test-categories');
        $data = $request->data ?? 10;

        Videoteca::truncate();
        DB::table('videoteca_module')->truncate();
        DB::table('videoteca_tag')->truncate();
        Taxonomy::group('videoteca')->type('categoria')->forceDelete();
        Taxonomy::group('videoteca')->type('tag')->forceDelete();
        UsuarioAccion::where('model_type', 'App\Videoteca')->forceDelete();

        $fakeTags = [];
        foreach ($tags as $i => $tag) {
            $fakeTags[] = [
                'group' => 'videoteca',
                'type' => 'tag',
                'active' => 1,
                'name' => $tag
            ];
        }
        DB::table('taxonomies')->insert($fakeTags);

        $fakeCategories = [];
        foreach ($categories as $i => $categorie) {
            $fakeCategories[] = [
                'group' => 'videoteca',
                'type' => 'categoria',
                'name' => $categorie
            ];
        }

        DB::table('taxonomies')->insert($fakeCategories);

        $fakeData = [];
        for ($i = 0; $i < $data; $i++) {
            $preview = Media::where('ext', 'png')->inRandomOrder()->first();

            $fakeData[] = [
                'title' => $faker->name,
                'description' => $faker->paragraph,
                'category_id' => Taxonomy::group('videoteca')->type('categoria')->inRandomOrder()->first()->id,
                'media_video' => $faker->sentence,
                'media_type' => $faker->randomElement([
                    'youtube', 'vimeo',
                ]),
                'preview_id' => $preview->id,
                'active' => 1
            ];
        }
        DB::table('videoteca')->insert($fakeData);

        $media_model = new Media();
        $fakeData = [];
        for ($i = 0; $i < $data; $i++) {
            $media = Media::inRandomOrder()->first();
            $preview = Media::where('ext', 'png')->inRandomOrder()->first();
            $fakeData[] = [
                'title' => $faker->name,
                'description' => $faker->paragraph,
                'category_id' => Taxonomy::group('videoteca')->type('categoria')->inRandomOrder()->first()->id,
                'media_video' => null,
                'media_id' => $media->id,
                'preview_id' => $preview->id,
                'media_type' => $media_model->getMediaType($media->ext),
                'active' => 1
            ];
        }
        DB::table('videoteca')->insert($fakeData);

        $videotecas = Videoteca::all();
        $accion_visita = Taxonomy::where('group', 'videoteca')
                                 ->where('type', 'accion')
                                 ->where('name', 'view')
                                 ->first();

        if (!$accion_visita) {
            $accion_visita = Taxonomy::create([
                'group' => 'videoteca',
                'type' => 'accion',
                'name' => 'view',
                'active' => 1
            ]);
        }

        foreach ($videotecas as $videoteca) {

            $tags_id = Taxonomy::videotecaTags()
                               ->inRandomOrder()
                               // ->limit(random_int(1, count($tags)))
                               ->limit(random_int(1, 10))
                               ->pluck('id');

            $videoteca->tags()->sync($tags_id);

            $modules = ['4', '5', '6'];
            // $modules = $faker->randomElements(['4', '5', '6']);
            $videoteca->modulos()->sync($modules);

            $videoteca->load('modules');
            $videoteca->load('tags');
            $videoteca->load('categoria');

            $user = Usuario::inRandomOrder()->where('estado', 1)->limit(random_int(1, 10))->pluck('id');
            $user->each(function ($user_id) use ($videoteca, $accion_visita) {
                $videoteca->incrementAction($accion_visita->id, $user_id, random_int(1, 1000));
            });
        }

        return $videotecas;
    }

    /*

        Methods for videoteca routes

    -------------------------------------------------------------------------*/


    /**
     * Get items list for select inputs
     *
     * @return JsonResponse
     */
    public function getListSelects()
    {
        $modules = Criterion::getValuesForSelect('module', true);
        $categorias = Taxonomy::getDataForSelect('videoteca', 'categoria');

        $tags = Taxonomy::group('videoteca')
                        ->type('tag')
                        ->orderBy('name', 'ASC')
                        ->get();

        return $this->success(get_defined_vars());
    }


    /**
     * Process request to load data for create form
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        // $modules = Criterion::getValuesForSelect('module');
        $modules = Workspace::loadSubWorkspaces(['id', 'name as nombre']);
        $categorias = Taxonomy::getDataForSelectWorkspace('videoteca', 'categoria');
        $tags = Taxonomy::getDataForSelectWorkspace('videoteca', 'tag');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $request->merge(['workspace_id' => get_current_workspace()->id ]);

        $items = Videoteca::search($request, false, $request->paginate);
        VideotecaResource::collection($items);

        // $data = Videoteca::prepareData($items->items());

        // $videoteca = [
        //     'current_page' => $items->currentPage(),
        //     'last_page' => $items->lastPage(),
        //     'videoteca' => $data,
        // ];

        return $this->success($items);

        // $modules = Abconfig::select('etapa', 'id')->get();
        // $categories = Taxonomia::group('videoteca')->type('categoria')->orderBy('nombre', 'ASC')->get();
        // $tags = Taxonomia::group('videoteca')->type('tag')->orderBy('nombre', 'ASC')->get();

        // return response()->json(compact('videoteca', 'modules', 'categories', 'tags'));
    }

    // todo: check usage
    public function show(Videoteca $videoteca)
    {
        $videoteca->load('media');
        $videoteca->load('preview');
        $videoteca->load('modules');
        $videoteca->modules = $videoteca->modules->makeHidden('pivot');
        $videoteca->load('tags');
        $videoteca->load('categoria');

        return response()->json(compact('videoteca'));
    }

    /**
     * Process request to store a record
     *
     * @param VideotecaStoreRequest $request
     * @return JsonResponse
     */
    public function store(VideotecaStoreRequest $request)
    {
        $data = $request->validated();

        $files = [];
        if (isset($data['file_media'])) $files[] = $data['file_media'];
        if (isset($data['file_preview'])) $files[] = $data['file_preview'];
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);

        if ($hasStorageAvailable) {

            $data = Media::requestUploadFileForId($data, 'media', $data['title'] ?? null);
            $data = Media::requestUploadFileForId($data, 'preview', $data['title'] ?? null);

            $videoteca = Videoteca::storeRequest($data);

            $msg = 'Videoteca creada correctamente.';

            return response()->json(compact('videoteca', 'msg', 'data'));

        } else {

            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }
    }

    /**
     * Process request to load data for edit form
     *
     * @param Videoteca $videoteca
     * @return JsonResponse
     */
    public function edit(Videoteca $videoteca)
    {

        // $media_types = config('constantes.media-types')
        $videoteca->load(
            'modules',
            'tags',
            'media',
            'preview'
        );

        $videoteca->media_type = config('constantes.media-types')[$videoteca->media_type];

//        $videoteca->makeHidden('media_id');
//        $videoteca->makeHidden('preview_id');

//        $videoteca->media = $videoteca->media()->pluck('file');
//        $videoteca->preview = $videoteca->preview->file;

        // $modules = Criterion::getValuesForSelect('module');
        $modules = Workspace::loadSubWorkspaces(['id', 'name as nombre']);
        $categorias = Taxonomy::getDataForSelectWorkspace(
            'videoteca', 'categoria'
        );

        $tags = Taxonomy::getDataForSelectWorkspace(
            'videoteca', 'tag'
        );

        $response = [
            'categorias' => $categorias,
            'modules' => $modules,
            'tags' => $tags,
            'videoteca' => [
                'active' => $videoteca->active,
                'category_id' =>$videoteca->category_id,
                'description' => $videoteca->description,
                'id' => $videoteca->id,
                'media' => $videoteca->media->file ?? null,
                'media_type' => $videoteca->media_type,
                'media_video' => $videoteca->media_video,
                'modules' => $videoteca->modules,
                'preview' => $videoteca->preview->file ?? null,
                'tags' => $videoteca->tags,
                'title' => $videoteca->title,
            ]
        ];

        return $this->success($response);
    }

    /**
     * Process request to update the specified record
     *
     * @param VideotecaStoreRequest $request
     * @param Videoteca $videoteca
     * @return JsonResponse
     */
    public function update(VideotecaStoreRequest $request, Videoteca $videoteca)
    {
        $data = $request->validated();

        $files = [];
        if (isset($data['file_media'])) $files[] = $data['file_media'];
        if (isset($data['file_preview'])) $files[] = $data['file_preview'];
        $hasStorageAvailable = Media::validateStorageByWorkspace($files);

        if ($hasStorageAvailable) {

            $data = Media::requestUploadFileForId($data, 'media', $data['title'] ?? null);
            $data = Media::requestUploadFileForId($data, 'preview', $data['title'] ?? null);

            $videoteca = Videoteca::storeRequest($data, $videoteca);
            $msg = 'Videoteca actualizada correctamente.';

            return response()->json(compact('videoteca', 'msg', 'data'));

        } else {

            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }
    }

    /**
     * Process request to toggle poll status between 1 or 0
     *
     * @param Videoteca $videoteca
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Videoteca $videoteca, Request $request)
    {
        $videoteca->update(['active' => !$videoteca->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /**
     * Process request to delete record
     *
     * @param Videoteca $videoteca
     * @return JsonResponse
     */
    public function delete(Videoteca $videoteca)
    {
        $videoteca->delete();

        return $this->success(['msg' => 'Videoteca eliminada correctamente.']);
    }

    /*

        Methods for tags routes

    -------------------------------------------------------------------------*/

    /**
     * Process request to load videoteca tags
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tagsList(Request $request)
    {
        $query = Taxonomy::videotecaTags( get_current_workspace()->id );

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        $tags = $query->paginate(20);

        return response()->json(compact('tags'));
    }

    /**
     * Process request to delete record
     *
     * @param Taxonomy $tag
     * @return JsonResponse
     */
    public function tagDelete(Taxonomy $tag)
    {
        DB::table('videoteca_tag')
            ->where('tag_id', $tag->id)
            ->delete();

        $tag->delete();
        return response()->json(['msg' => 'Tag eliminado.']);
    }

    /**
     * Process request to update the specified record
     *
     * @param Taxonomy $tag
     * @param Request $request
     * @return JsonResponse
     */
    public function tagEdit(Taxonomy $tag, Request $request)
    {
        $data = $request->all();
        $tag->update($data);
        return $this->success(['msg' => 'Tag editado correctamente']);
    }

    /*

        Methods for categories routes

    -------------------------------------------------------------------------*/

    /**
     *
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categoriasList(Request $request)
    {
        $query = Taxonomy::videotecaCategories(get_current_workspace()->id)
                         ->orderBy('created_at', 'DESC');

        if ($request->q)
            $query->where('name', 'like', "%{$request->q}%");

        $categorias_videoteca = $query->paginate(15);

        return response()->json(compact('categorias_videoteca'));
    }

    /**
     * Process request to create a new record
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categoriasStore(Request $request)
    {
        $data = $request->all();
        $data['workspace_id'] = get_current_workspace()->id;
        $data['group'] = 'videoteca';
        $data['type'] = 'categoria';
        $data['active'] = 1;
        Taxonomy::create($data);

        return $this->success(['msg' => 'Categoria creada correctamente']);
    }

    /**
     * Process request to udpate record
     *
     * @param Taxonomy $tag
     * @param Request $request
     * @return JsonResponse
     */
    public function categoriasEdit(Request $request)
    {
        $data = $request->all();
        Taxonomy::find($request->id)->update($data); //change

        return $this->success(['msg' => 'Categoria editada correctamente']);
    }

    // todo: check usage
    public function categorias_create()
    {
        return view('videoteca.categorias.create');
    }

    // todo: check usage
    public function categorias_store(Request $request)
    {
        $nombre = $request->nombre;
        Taxonomy::create([
            'type' => 'categoria',
            'group' => 'videoteca',
            'name' => $nombre,
            'active' => 1
        ]);
        return redirect()->route('videoteca.categorias')
            ->with('info', 'Categoría creada.');
    }

    // todo: check usage
    public function categorias_edit($id)
    {
        $elemento = Taxonomy::find($id);
        return view(
            'videoteca.categorias.edit', compact('elemento')
        );
    }

    // todo: check usage
    public function categorias_update(Request $request, $id)
    {
        $taxonomy = Taxonomy::find($id);
        $taxonomy->name = $request->nombre;
        $taxonomy->save();

        return redirect()->route('videoteca.categorias')
            ->with('info', 'Categoría actualizada.');
    }

    // todo: check usage
    public function categorias_destroy($id)
    {
        $taxonomy = Taxonomy::find($id);
        $taxonomy->delete();

        return redirect()->route('videoteca.categorias')
            ->with('info', 'Categoría eliminada.');
    }




}
