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

        $glossary = Glossary::where('name', $name)->first();

        if ( $glossary ) {

            $module = $glossary->modules()
                               ->wherePivot('module_id', $this->modulo_id)
                               ->first();

            if ($module) {

                if ($module->pivot->codigo != $code) {

                    $name = $name . ' DUPLICATED-NAME-' . now()->format('Y-m-dH:i:s');

                    // Crear nuevo glosario

                    $glossary = $this->createGlossary($name, $code, $row);
                }

            } else {

                // Relacionar glosario con el módulo

                $glossary->modules()
                         ->attach($this->modulo_id, ['code' => $code]);
            }

        } else {

            // Crear nuevo glosario

            $glossary = $this->createGlossary($name, $code, $row);
        }
    }

    public function createGlossary($name, $code, $row)
    {
        $data['name'] = $name;
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

        $data['active'] = 1;

        $glossary = Glossary::create($data);

        $principios_activos = $this->getPrincipiosActivos($row);

        if ( $principios_activos )
            $glossary->principios_activos()->attach($principios_activos);

        $contraindicaciones = $this->getTaxonomiesId($row, 'contraindicacion');

        if ( $contraindicaciones )
            $glossary->contraindicaciones()->attach($contraindicaciones);

        $interacciones = $this->getTaxonomiesId($row, 'interaccion', 'interacciones_frecuentes');

        if ( $interacciones )
            $glossary->interacciones()->attach($interacciones);

        $reacciones = $this->getTaxonomiesId($row, 'reaccion', 'reacciones_frecuentes');

        if ( $reacciones )
            $glossary->reacciones()->attach($reacciones);

        $glossary->modules()->attach($this->modulo_id, ['code' => $code]);

        return $glossary;
    }

    public function getPrincipiosActivos($row)
    {
        $principios_activos = [];

        for ($i = 1; $i <= 5; $i++ ) {

            $principioActivoId = $this->getTaxonomyId(
                $row, 'principio_activo', 'principio_activo_' . $i
            );

            if ($principioActivoId) {
                $principios_activos[$principioActivoId] = [
                    'glossary_group_id' => Glossary::GRUPOS['principio_activo']
                ];
            }
        }

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

                if ( $id ) {
                    $ids[$id] = [
                        'glossary_group_id' => Glossary::GRUPOS[$type]
                    ];
                }

            endforeach;

        endif;

        return $ids;
    }

    public function getTaxonomyId($row, $type, $fieldname = null)
    {
        $fieldname = $fieldname ?? $type;

        if ( ! empty($row[$fieldname]) ) {

            $name = trim($row[$fieldname]);
            $taxonomy = Taxonomy::getOrCreate('glosario', $type, $name);

            return $taxonomy->id;

        }

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
