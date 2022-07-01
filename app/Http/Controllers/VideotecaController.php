<?php

namespace App\Http\Controllers;

use App\Http\Requests\Videoteca\VideotecaStoreRequest;

use App\Models\Media;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Videoteca;
use App\Models\Taxonomia;
use App\Models\UsuarioAccion;

use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VideotecaResource;
use function foo\func;

class VideotecaController extends Controller
{

    public function fakeData(Request $request)
    {
        $faker = Faker::create('es_ES');

        $tags = config('constantes.videoteca-test-tags');
        $categories = config('constantes.videoteca-test-categories');
        $data = $request->data ?? 10;

        Videoteca::truncate();
        DB::table('videoteca_module')->truncate();
        DB::table('videoteca_tag')->truncate();
        Taxonomia::group('videoteca')->type('categoria')->forceDelete();
        Taxonomia::group('videoteca')->type('tag')->forceDelete();
        UsuarioAccion::where('model_type', 'App\Videoteca')->forceDelete();

        $fakeTags = [];
        foreach ($tags as $i => $tag) {
            $fakeTags[] = [
                'grupo' => 'videoteca',
                'tipo' => 'tag',
                'estado' => 1,
                'nombre' => $tag
            ];
        }
        DB::table('taxonomias')->insert($fakeTags);

        $fakeCategories = [];
        foreach ($categories as $i => $categorie) {
            $fakeCategories[] = [
                'grupo' => 'videoteca',
                'tipo' => 'categoria',
                'nombre' => $categorie
            ];
        }

        DB::table('taxonomias')->insert($fakeCategories);

        $fakeData = [];
        for ($i = 0; $i < $data; $i++) {
            $preview = Media::where('ext', 'png')->inRandomOrder()->first();

            $fakeData[] = [
                'title' => $faker->name,
                'description' => $faker->paragraph,
                'category_id' => Taxonomia::group('videoteca')->type('categoria')->inRandomOrder()->first()->id,
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
                'category_id' => Taxonomia::group('videoteca')->type('categoria')->inRandomOrder()->first()->id,
                'media_video' => null,
                'media_id' => $media->id,
                'preview_id' => $preview->id,
                'media_type' => $media_model->getMediaType($media->ext),
                'active' => 1
            ];
        }
        DB::table('videoteca')->insert($fakeData);

        $videotecas = Videoteca::all();
        $accion_visita = Taxonomia::where('grupo', 'videoteca')->where('tipo', 'accion')->where('nombre', 'view')->first();
        if (!$accion_visita) {
            $accion_visita = Taxonomia::create([
                'grupo' => 'videoteca',
                'tipo' => 'accion',
                'nombre' => 'view',
                'estado' => 1
            ]);
        }

        foreach ($videotecas as $videoteca) {
            $tags_id = Taxonomia::videotecaTags()
                ->inRandomOrder()
                // ->limit(random_int(1, count($tags)))
                ->limit(random_int(1, 10))
                ->pluck('id');
            $videoteca->tags()->sync($tags_id);

            $modules = ['4', '5', '6'];
            // $modules = $faker->randomElements(['4', '5', '6']);
            $videoteca->modules()->sync($modules);

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

    public function getListSelects()
    {
        $modules = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::group('videoteca')->type('categoria')->orderBy('nombre', 'ASC')->get();
        $tags = Taxonomia::group('videoteca')->type('tag')->orderBy('nombre', 'ASC')->get();

        return $this->success(get_defined_vars());
    }

    public function create(Request $request)
    {
        $modules = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::group('videoteca')->type('categoria')->orderBy('nombre', 'ASC')->get();
        $tags = Taxonomia::group('videoteca')->type('tag')->orderBy('nombre', 'ASC')->get();

        return $this->success(get_defined_vars());
    }

    public function search(Request $request)
    {
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

    public function store(VideotecaStoreRequest $request)
    {
        $data = $request->validated();

        $data = Media::requestUploadFileForId($data, 'media', $data['title'] ?? null);
        $data = Media::requestUploadFileForId($data, 'preview', $data['title'] ?? null);

//        dd($data);

        $videoteca = Videoteca::storeRequest($data);

        $msg = 'Videoteca creada correctamente.';

        return response()->json(compact('videoteca', 'msg', 'data'));
    }

    public function edit(Videoteca $videoteca)
    {
//        $videoteca->load('media', 'preview');
        // $media_types = config('constantes.media-types')
        $videoteca->load('modules', 'tags', 'media', 'preview');

        $videoteca->media_type = config('constantes.media-types')[$videoteca->media_type];

//        $videoteca->makeHidden('media_id');
//        $videoteca->makeHidden('preview_id');

//        $videoteca->media = $videoteca->media()->pluck('file');
//        $videoteca->preview = $videoteca->preview->file;

        $modules = Abconfig::getModulesForSelect();
        $categorias = Taxonomia::group('videoteca')->type('categoria')->orderBy('nombre', 'ASC')->get();
        $tags = Taxonomia::group('videoteca')->type('tag')->orderBy('nombre', 'ASC')->get();

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

//        return $this->success(get_defined_vars());
        return $this->success($response);
    }

    public function update(VideotecaStoreRequest $request, Videoteca $videoteca)
    {
        $data = $request->validated();

        $data = Media::requestUploadFileForId($data, 'media', $data['title'] ?? null);
        $data = Media::requestUploadFileForId($data, 'preview', $data['title'] ?? null);

//        dd($data);

        $videoteca = Videoteca::storeRequest($data, $videoteca);
        $msg = 'Videoteca actualizada correctamente.';

        return response()->json(compact('videoteca', 'msg', 'data'));

    }

    public function status(Videoteca $videoteca, Request $request)
    {
        $videoteca->update(['active' => !$videoteca->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function delete(Videoteca $videoteca)
    {
        $videoteca->delete();

        return $this->success(['msg' => 'Videoteca eliminada correctamente.']);
    }

    public function tagsList(Request $request)
    {
        $query = Taxonomia::videotecaTags()->select('id', 'nombre');

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");

        $tags = $query->paginate(20);

        return response()->json(compact('tags'));
    }

    public function tagDelete(Taxonomia $tag)
    {
        DB::table('videoteca_tag')->where('tag_id', $tag->id)->delete();
        $tag->delete();
        return response()->json(['msg' => 'Tag eliminado.']);
    }

    //Categorias
    public function categoriasList(Request $request)
    {
        $query = Taxonomia::categoriaVideoteca()->orderBy('created_at', 'DESC');

        if ($request->q)
            $query->where('nombre', 'like', "%{$request->q}%");
        $categorias_videoteca = $query->paginate(15);

        return response()->json(compact('categorias_videoteca'));
    }

    public function categorias_create()
    {
        return view('videoteca.categorias.create');
    }

    public function categorias_store(Request $request)
    {
        $nombre = $request->nombre;
        Taxonomia::create([
            'tipo' => 'categoria',
            'grupo' => 'videoteca',
            'nombre' => $nombre,
            'estado' => 1
        ]);
        return redirect()->route('videoteca.categorias')
            ->with('info', 'Categoría creada.');
    }

    public function categorias_edit($id)
    {
        $elemento = Taxonomia::find($id);
        return view('videoteca.categorias.edit', compact('elemento'));
    }

    public function categorias_update(Request $request, $id)
    {
        $taxonomia = Taxonomia::find($id);
        $taxonomia->nombre = $request->nombre;
        $taxonomia->save();

        return redirect()->route('videoteca.categorias')
            ->with('info', 'Categoría actualizada.');
    }

    public function categorias_destroy($id)
    {
        $taxonomia = Taxonomia::find($id);
        $taxonomia->delete();

        return redirect()->route('videoteca.categorias')
            ->with('info', 'Categoría eliminada.');
    }

    public function tagEdit(Taxonomia $tag, Request $request)
    {
        $data = $request->all();
        $tag->update($data);
        return $this->success(['msg' => 'Tag editado correctamente']);
    }

    public function categoriasStore(Request $request)
    {
        $data = $request->all();
        $data['grupo'] = 'videoteca';
        $data['tipo'] = 'categoria';
        $data['estado'] = 1;
        Taxonomia::create($data);
        return $this->success(['msg' => 'Categoria creada correctamente']);
    }

    public function categoriasEdit(Taxonomia $tag, Request $request)
    {
        $data = $request->all();
        $tag->update($data);
        return $this->success(['msg' => 'Categoria editada correctamente']);
    }
}
