<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeederServer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Taxonomy::create([
            'group' => 'server',
            'type' => 'service',
            'code' => 'aws',
            'name' => 'Amazon',
            'active' => ACTIVE,
            'icon' => 'fa-brands fa-aws',
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'service',
            'code' => 'do',
            'name' => 'Digital Ocean',
            'active' => ACTIVE,
            'icon' => 'fa-brands fa-digital-ocean',
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'service',
            'code' => 'linode',
            'name' => 'Linode',
            'active' => ACTIVE,
            'icon' => 'fa-brands fa-linode',
            'position' => 3,
        ]);

        

        Taxonomy::create([
            'group' => 'server',
            'type' => 'type',
            'code' => 'database',
            'name' => 'Database',
            'active' => ACTIVE,
            'position' => 1,
            'icon' => 'fa-solid fa-database',
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'type',
            'code' => 'instance',
            'name' => 'Instancia',
            'active' => ACTIVE,
            'position' => 2,
            'icon' => 'fa-solid fa-server',
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'type',
            'code' => 'redis',
            'name' => 'Redis',
            'active' => ACTIVE,
            'position' => 3,
            'icon' => 'fa-solid fa-registered',
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'type',
            'code' => 'storage',
            'name' => 'Storage',
            'active' => ACTIVE,
            'position' => 4,
            'icon' => 'storage',
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'type',
            'code' => 'worker',
            'name' => 'Worker',
            'active' => ACTIVE,
            'position' => 5,
            'icon' => 'fa-solid fa-gears',
        ]);



        Taxonomy::create([
            'group' => 'server',
            'type' => 'limit_type',
            'code' => 'users',
            'name' => 'Cantidad de Usuarios',
            'alias' => '# Usuarios',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'limit_type',
            'code' => 'clients',
            'name' => 'Cantidad de Clientes',
            'alias' => '# Clientes',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'limit_type',
            'code' => 'storage',
            'name' => 'Tamaño de Almacenamiento',
            'alias' => 'Almacenamiento',
            'active' => ACTIVE,
            'position' => 3,
        ]);



        Taxonomy::create([
            'group' => 'server',
            'type' => 'status',
            'code' => 'available',
            'name' => 'Disponible',
            'active' => ACTIVE,
            'position' => 1,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'status',
            'code' => 'unavailable',
            'name' => 'No disponible aún',
            'active' => ACTIVE,
            'position' => 2,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'status',
            'code' => 'reserved',
            'name' => 'Reservado',
            'active' => ACTIVE,
            'position' => 3,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'status',
            'code' => 'full',
            'name' => 'Lleno',
            'active' => ACTIVE,
            'position' => 4,
        ]);

        Taxonomy::create([
            'group' => 'server',
            'type' => 'status',
            'code' => 'down',
            'name' => 'De baja',
            'active' => ACTIVE,
            'position' => 5,
        ]);

      

    }
}
