<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Videoteca extends BaseModel
{
    protected $table = 'videoteca';

    protected $fillable = [
        'title',
        'description',
        'media_video',
        'media_type',
        'media_id',
        'preview_id',
        'category_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function media()
    {
        return $this->hasOne(Media::class, 'id', 'media_id')
            ->select('id', 'title', 'file', 'ext');
    }

    public function preview()
    {
        return $this->hasOne(Media::class, 'id', 'preview_id')
            ->select('id', 'title', 'file', 'ext');
    }

    public function modules()
    {
        return $this->belongsToMany(Abconfig::class, 'videoteca_module', 'videoteca_id', 'module_id')
            ->select('etapa', 'id');
    }

    public function categoria()
    {
        return $this->belongsTo(Taxonomy::class, 'category_id')
                    ->select('name', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(
                Taxonomy::class,
                'videoteca_tag',
                'videoteca_id',
                'tag_id');
    }

    public function actions()
    {
        return $this->morphMany(UsuarioAccion::class, 'model');
    }

    protected function getViews($videoteca)
    {
        return $videoteca->actions()->sum('score');
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value==='true' OR $value === true OR $value === 1 OR $value === '1' );
    }

    protected function search($request, $api = false, $paginate = 20)
    {
        $relationships = [
//            'modules',
            'media', 'categoria', 'tags', 'preview'];

        if ($api) :
            $request->active = 1;
            $relationships = [
//                'modules' => function ($q) use ($request) {
//                    if ($request->modulo_id)
//                        $q->where('id', $request->modulo_id);
//                },
                'preview',
                'media',
                'categoria',
                'tags',
            ];
        endif;

        $query = self::with($relationships);

        if ($request->q)
            $query->where('title', 'like', "%{$request->q}%");

        if ($request->modulo_id)
            $query->whereHas('modules', function ($q) use ($request) {
                $q->where('id', $request->modulo_id);
            });

        if ($request->categoria_id)
            $query->where('category_id', $request->categoria_id);

        if ($request->tag)
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('id', $request->tag);
            });

        if ($request->active)
            $query->where('active', $request->active);

        if ($request->no_id)
            $query->whereNotIn('id', [$request->no_id]);

        return $query->latest('id')->paginate($paginate);
    }

    public function incrementAction($type_id, $user_id, $quantity = 1)
    {
        $row = $this->actions()
            ->firstOrCreate(compact('user_id', 'type_id'));
//        info("[incrementAction] ", [$row]);
        $row->increment('score', $quantity);
        return $row;
    }

    protected function storeRequest($data = [], $videoteca = null, $files = [])
    {
        try {

            DB::beginTransaction();

            if ($videoteca) :
                // $videoteca->fill($data);
                // if ($videoteca->isDirty()):
                //     $videoteca->save($data);
                // endif;

                $videoteca->update($data);
            else:
                $videoteca = self::create($data);
            endif;

            // if (!empty($data['modules'])) :
                $videoteca->modules()->sync($data['modules'] ?? []);
            // endif;



            $tags = $this->prepareTaxonomiesData($data, 'tags', 'tag');
            $videoteca->tags()->sync($tags);

            // $videoteca->save();

            DB::commit();

            return $videoteca;

        } catch (\Exception $e) {

            info($e);
            DB::rollBack();
        }
    }

    protected function prepareData($items, $with = [], $user = null)
    {
        $data = [];

        foreach ($items as $key => $row) {
            $data[] = $this->processData($row, $with, $user);
        }

        return $data;
    }

    protected function processData(Videoteca $data, $with): array
    {
        $processedData = [
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? 'Sin descripción',
            'views' => $this->getViews($data),
            'categoria' => $data['categoria'],
            'media_type' => $data['media_type'],
            'contenido' => [
                'preview' => $this->getPreview($data),
                'tipo' => $data['media_type'],
                'string' => in_array($data['media_type'], ['youtube', 'vimeo'], true) ?
                    $data['media_video']
                    : $data['media']['file']
            ],
            'tags' => $data['tags'],
            'active' => $data['active']
        ];

        if (in_array('related_tags', $with, true)):
            $tags = $this->getRelatedTags($data);
            $processedData['tags_related'] = $tags;
        endif;

        return $processedData;
    }

    public function getPreview(Videoteca $videoteca = null)
    {
        $videoteca = $videoteca ?? $this;

        if (!$videoteca->preview_id):
            if (in_array($videoteca->media_type, ['youtube', 'vimeo', 'video'], true)) {
                $preview = Media::DEFAULT_VIDEO_IMG;
            } else {
                switch ($videoteca->media_type) {
                    case 'audio':
                        $preview = Media::DEFAULT_AUDIO_IMG;
                        break;
                    case 'scorm':
                        $preview = Media::DEFAULT_SCORM_IMG;
                        break;
                    case 'pdf':
                        $preview = Media::DEFAULT_PDF_IMG;
                        break;
                    default: // Imagen
                        $preview = $videoteca->media->file ?? '';
                        break;
                }
            }
            return $preview;
        endif;

        return $videoteca->preview->file;
    }

    protected function getRelated($videoteca, $user = null, $paginate = 20)
    {
        $tags = $this->getRelatedTags($videoteca);
        $tags_ids = array_column($tags, 'id');

        $query = self::with('categoria', 'tags')
            ->whereHas('tags', function ($q) use ($tags_ids) {
                $q->whereIn('id', $tags_ids);
            })
            ->whereNotIn('id', [$videoteca->id])
            ->where('active', 1)
            ->inRandomOrder();

        if ($user)
            $query->whereHas('modules', function ($q) use ($user) {
                $q->where('id', $user->config_id);
            });

        $result = $query->paginate($paginate);

        $related = $this->prepareData($result->items(), [], $user);

        return [
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'data' => $related,
        ];
    }

    protected function getRelatedTags($videoteca): array
    {
        $tags = collect();
        $query = self::query()
            ->whereHas('tags', function ($q) use ($videoteca) {
                $q->whereIn('id', $videoteca->tags->pluck('id'));
            })
            ->whereNotIn('id', [$videoteca->id])
            ->where('active', 1);

        $first_related = $query->get();
        foreach ($first_related->pluck('tags') as $key => $tag_groups) {
            foreach ($tag_groups as $tag) {
                $tags->push($tag);
            }
        }
        $unique = $tags->unique(function ($item) {
            return $item['id'];
        });

        return $unique->sortBy('id')->values()->all();
    }

    public function prepareTaxonomiesData($data, $key, $type)
    {
        $ids = [];

        if (!empty($data[$key])) :

            foreach ($data[$key] as $i => $value) :

                $taxonomia = $this->getOrCreateTaxonomyByValue($type, $value);

                $ids[] = $taxonomia->id;

            endforeach;

        endif;

        return $ids;
    }

    public function getOrCreateTaxonomyByValue($type, $value)
    {
        $value = trim($value);

        if (is_numeric($value)) {

            return Taxonomy::where('group', 'videoteca')
                            ->where('type', $type)
                            ->where('id', $value)
                            ->first();
        }

        return Taxonomy::getOrCreate('videoteca', $type, $value);
    }
}
