<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederSettingType extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $field_date = Taxonomy::create([
            'group' => 'setting',
            'type' => 'field',
            'code' => 'date',
            'name' => 'Fecha',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        $field_number = Taxonomy::create([
            'group' => 'setting',
            'type' => 'field',
            'code' => 'integer',
            'name' => 'Numérico Entero',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        $field_text = Taxonomy::create([
            'group' => 'setting',
            'type' => 'field',
            'code' => 'text',
            'name' => 'Texto',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        $field_datetime = Taxonomy::create([
            'group' => 'setting',
            'type' => 'field',
            'code' => 'datetime',
            'name' => 'Fecha y Hora',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        $field_boolean = Taxonomy::create([
            'group' => 'setting',
            'type' => 'field',
            'code' => 'boolean',
            'name' => 'Booleano',
            'active' => ACTIVE,
            'position' => 5,
        ]);

        $field_decimal = Taxonomy::create([
            'group' => 'setting',
            'type' => 'field',
            'code' => 'decimal',
            'name' => 'Númerico Decimal',
            'active' => ACTIVE,
            'position' => 6,
        ]);

    }
}
