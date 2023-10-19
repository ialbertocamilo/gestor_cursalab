<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarUsuarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usuarios:migrar_master';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los usuarios de inretail a master';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $correo_nulo = 1;

        // Conexión a la base de datos de origen
        $origen = DB::connection('mysql');

        // Conexión a la base de datos de destino
        $destino = DB::connection('mysql_master');

        // Obtener los usuarios de la base de datos de origen
        $usuarios = $origen->select("SELECT document, email, username FROM users WHERE subworkspace_id IS NOT NULL AND type_id IN (select id from taxonomies t where t.group='user' and t.type='type' and t.code='client');");

        foreach ($usuarios as $usuario) {
            // Verificar si el usuario ya existe en la base de datos de destino
            $existingUser = $destino
                ->table('master_usuarios')
                ->where(function ($query) use ($usuario) {
                    $query->where('dni', $usuario->document)
                        ->orWhere('email', $usuario->email);
                })
                ->first();

            if ($existingUser) {
                // Si el usuario ya existe, salta al siguiente usuario
                info('Usuario ya existe: ' . $usuario->document . $usuario->email??'');
                continue;
            }

            if ($usuario->email == '') {
                $destino->table('master_usuarios')->insert([
                    'dni' => $usuario->document,
                    'username' => $usuario->username,
                    // 'email' => 'sin_correo' . $correo_nulo++,
                    'customer_id' => ENV('CUSTOMER_ID'),
                ]);
                info('Usuario migrado: ' . $usuario->document);
            } else {
                $destino->table('master_usuarios')->insert([
                    'dni' => $usuario->document,
                    'username' => $usuario->username,
                    'email' => $usuario->email,
                    'customer_id' => ENV('CUSTOMER_ID'),
                ]);
                info('Usuario migrado: ' . $usuario->document);
            }
        }

        $this->info('Usuarios migrados con éxito.');
        return Command::SUCCESS;
    }
}
