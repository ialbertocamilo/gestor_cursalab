<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Categoria extends Model
{


    protected $fillable = [
        'config_id', 'nombre', 'descripcion', 'imagen', 'estado', 'orden', 'modalidad', 'color', 'reinicios_programado', 'plantilla_diploma'
    ];

    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function temas()
    {
        return $this->hasMany(Posteo::class, 'categoria_id');
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'categoria_id');
    }

    public function config()
    {
        return $this->belongsTo(Abconfig::class, 'config_id');
    }

    public function cate_perfiles()
    {
        return $this->hasMany(Categoria_perfil::class, 'categoria_id');
    }

    public function delete()
    {
        $this->temas()->delete();
        // $this->temas()->delete();

        return parent::delete();
    }

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente'
        ];
    }
    /* AUDIT TAGS */

    protected static function search($request)
    {
        $query = self::where('config_id', $request->modulo_id)->withCount(['cursos']);

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    public function guardarNombreCiclo0($categoria_id, $nombre)
    {
        if (!empty($nombre)) {
            $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $categoria_id)->first();
            if ($nombre_ciclo_0) {
                $nombre_ciclo_0 = DB::table('nombre_escuelas')->where('escuela_id', $categoria_id)->update(['nombre' => $nombre]);
            } else {
                $nombre_ciclo_0 = DB::table('nombre_escuelas')->insert([
                    'escuela_id' => $categoria_id,
                    'nombre' => $nombre,
                ]);
            }
        }
    }

    protected static function storeRequest($data, $categoria = null)
    {
        try {

            DB::beginTransaction();

            if ($categoria) :
                $categoria->update($data);
            else :
                $categoria = self::create($data);
            endif;

            if (!empty($data['file_imagen'])) :
                $path = Media::uploadFile($data['file_imagen']);
                $categoria->imagen = $path;
            endif;

            if (!empty($data['file_plantilla_diploma'])) :
                $path = Media::uploadFile($data['file_plantilla_diploma']);
                $categoria->plantilla_diploma = $path;
            endif;

            if (!empty($data['nombre_ciclo_0'])) : (new Categoria())->guardarNombreCiclo0($categoria->id, $data['nombre_ciclo_0']);
            endif;


            $categoria->save();
            DB::commit();
            return $categoria;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    protected function validateEscuelaEliminar($escuela)
    {

        if (true) :

            return [
                'validate' => false,
                //                'data' => $validate,
                'type' => 'validate_posteo_eliminar',
                'title' => 'Ocurrió un problema'
            ];
        endif;

        return ['validate' => true];
    }
}
