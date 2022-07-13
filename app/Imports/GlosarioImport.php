<?php

namespace App\Imports;

use App\Models\Glossary;
use App\Models\Taxonomy;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class GlosarioImport implements WithHeadingRow, OnEachRow, WithValidation, WithChunkReading, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    public $modulo_id = NULL;
    public $categoria_id = NULL;

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        $name = trim($row['descripcion']);
        $code = trim($row['codigo']);

        $glosario = Glossary::where('nombre', $name)->first();

        if ( $glosario ) :

            $module = $glosario->modulos()->wherePivot('modulo_id', $this->modulo_id)->first();

            if ( $module ) :

                if ( $module->pivot->codigo != $code ):

                    $name = $name . ' DUPLICATED-NAME-' . now()->format('Y-m-dH:i:s');

                    // Crear nuevo glosario
                    $glosario = $this->createGlosario($name, $code, $row);

                endif;

            else:

                // Relacionar glosario con el módulo
                $glosario->modulos()->attach($this->modulo_id, ['codigo' => $code]);

            endif;

        else:

            // Crear nuevo glosario
            $glosario = $this->createGlosario($name, $code, $row);

        endif;
    }

    public function createGlosario($name, $code, $row)
    {
        $data['nombre'] = $name;
        $data['categoria_id'] = $this->categoria_id;

        $data['laboratorio_id'] = $this->getTaxonomyId($row, 'laboratorio');
        $data['jerarquia_id'] = $this->getTaxonomyId($row, 'jerarquia');
        $data['condicion_de_venta_id'] = $this->getTaxonomyId($row, 'condicion_de_venta');
        $data['via_de_administracion_id'] = $this->getTaxonomyId($row, 'via_de_administracion');
        $data['grupo_farmacologico_id'] = $this->getTaxonomyId($row, 'grupo_farmacologico');
        $data['dosis_adulto_id'] = $this->getTaxonomyId($row, 'dosis_adulto');
        $data['dosis_nino_id'] = $this->getTaxonomyId($row, 'dosis_nino');
        $data['recomendacion_de_administracion_id'] = $this->getTaxonomyId($row, 'recomendacion_de_administracion');
        $data['advertencias_id'] = $this->getTaxonomyId($row, 'advertencias');

        $data['estado'] = 1;

        $glosario = Glossary::create($data);

        $principios_activos = $this->getPrincipiosActivos($row);

        if ( $principios_activos )
            $glosario->principios_activos()->attach($principios_activos);

        $contraindicaciones = $this->getTaxonomiesId($row, 'contraindicacion');

        if ( $contraindicaciones )
            $glosario->contraindicaciones()->attach($contraindicaciones);

        $interacciones = $this->getTaxonomiesId($row, 'interaccion', 'interacciones_frecuentes');

        if ( $interacciones )
            $glosario->interacciones()->attach($interacciones);

        $reacciones = $this->getTaxonomiesId($row, 'reaccion', 'reacciones_frecuentes');

        if ( $reacciones )
            $glosario->reacciones()->attach($reacciones);

        $glosario->modulos()->attach($this->modulo_id, ['codigo' => $code]);

        return $glosario;
    }

    public function getPrincipiosActivos($row)
    {
        $principios_activos = [];

        for ($i = 1; $i <= 5; $i++ ):

            $pa_id = $this->getTaxonomyId($row, 'principio_activo', 'principio_activo_' . $i);

            if ( $pa_id )
                $principios_activos[$pa_id] = ['glosario_grupo_id' => Glossary::GRUPOS['principio_activo'] ];
        endfor;

        return $principios_activos;
    }

    public function getTaxonomiesId($row, $type, $fieldname = NULL)
    {
        $ids = [];

        $fieldname = $fieldname ?? $type;

        if ( !empty($row[$fieldname]) ) :

            $taxonomies = explode(',', $row[$fieldname]);

            foreach ($taxonomies as $key => $taxonomy) :

                $new[$type] = trim($taxonomy);

                $id = $this->getTaxonomyId($new, $type);

                if ( $id ) :
                    $ids[$id] = ['glosario_grupo_id' => Glossary::GRUPOS[$type]];
                endif;

            endforeach;

        endif;

        return $ids;
    }

    public function getTaxonomyId($row, $type, $fieldname = null)
    {
        $fieldname = $fieldname ?? $type;

        if ( ! empty($row[$fieldname]) ) :

            $name = trim($row[$fieldname]);
            $taxonomy = Taxonomy::getOrCreate('glosario', $type, $name);

            return $taxonomy->id;

        endif;

        return null;
    }

    public function  rules(): array
    {
        return [
            'codigo' => 'required|max:50',
            'descripcion' => 'required|max:255|distinct',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
