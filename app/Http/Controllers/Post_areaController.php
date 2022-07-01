<?php

namespace App\Http\Controllers;

use App\Models\Post_area;
use App\Models\Posteo;
use App\Models\Area;
use App\Http\Requests\Post_areaStoreRequest;
use Illuminate\Http\Request;

class Post_areaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post_areas = Post_area::paginate();

        return view('post_areas.index', compact('post_areas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $posteo_array = Posteo::select('id','nombre')->pluck('nombre','id' );
        $area_array = Area::select('id','nombre')->pluck('nombre','id' );
        return view('post_areas.create', compact('posteo_array','area_array'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Post_areaStoreRequest $request)
    {
        $post_area = Post_area::create($request->all());

        return redirect()->route('post_areas.index')
                ->with('info', 'Post_areao guardado con éxito');
    }

    public function edit(Post_area $post_area)
    {
        $posteo_array = Posteo::select('id','nombre')->pluck('nombre','id' );
        $area_array = Area::select('id','nombre')->pluck('nombre','id' );
        return view('post_areas.edit', compact('post_area', 'posteo_array','area_array'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post_area  $post_area
     * @return \Illuminate\Http\Response
     */
    public function update(Post_areaStoreRequest $request, Post_area $post_area)
    {
        $post_area->update($request->all());

        return redirect()->route('post_areas.index')
                ->with('info', 'Post_areao actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post_area  $post_area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post_area $post_area)
    {
        $post_area->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }
}
