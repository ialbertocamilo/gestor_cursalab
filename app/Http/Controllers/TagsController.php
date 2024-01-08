<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Taxonomy;
use App\Http\Requests\TagSR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TagResource;

class TagsController extends Controller
{

    public function search(Request $request)
    {
        $tags = Tag::search($request);

        TagResource::collection($tags);

        return $this->success($tags);
    }

    public function index(Request $request)
    {
        // if ($request->has('q')) {
        //     $question = $request->input('q');
        //     $tags = Tag::where('nombre', 'like', '%'.$question.'%')->paginate();
        // }else{
        //     $tags = Tag::paginate();
        // }
        // return view('tags.index', compact('tags'));
    }

    // Captura todos los tags dependiendo del tipo
    public function get(Request $request)
    {
        // $tipo = $request->tipo;
        // $tags = DB::select("SELECT t.id, t.nombre, t.color
        // FROM tags as t
        // inner join tag_relationships as tr on t.id=tr.tag_id
        // WHERE tr.element_type='$tipo'");
        $tags = DB::select("SELECT id, nombre, color FROM tags");
        return $tags;
    }

    public function create()
    {
        // return view('tags.create');
    }

    public function store(TagSR $request)
    {
        $data = $request->validated();
        $tag = Tag::storeRequest($data);
        return $this->success(['msg' => 'Tag creado correctamente.','tag'=>$tag]);
    }
    public function searchByType(Request $request){
        $data = $request->all();
        $tags = Taxonomy::where('group','tags')->where('type',$data['type'])->select('id','name','description')->get();
        return $this->success(['tags'=>$tags]);
    }
    public function edit(Tag $tag)
    {
        return $this->success(get_defined_vars());
    }

    public function update(TagSR $request, Tag $tag)
    {
        $data = $request->validated();

        $tag->update($data);

        return $this->success(['msg' => 'Tag actualizado correctamente.']);
    }

    public function destroy(Taxonomy $tag)
    {
        $hasTagRelation = Tag::where('tag_id',$tag->id)->first();
        if($hasTagRelation){
            return $this->error('No se puede eliminar este tag debido a que esta relacionado a un tema.', 422, ['errors' => ['No se puede eliminar este tag debido a que esta relacionado a un tema.']]);
        }
        $tag->delete();
        return $this->success(['msg' => 'Tag eliminado correctamente.']);
    }
}
