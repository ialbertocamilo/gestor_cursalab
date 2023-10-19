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
        // $usuarios = $origen->select('SELECT document,email,username FROM users where subworkspace_id > 33 and subworkspace_id not in (66, 88, 115, 166, 167, 168) and type_id = 4554;');
        // $usuarios = $origen->select('SELECT document,email,username FROM users where subworkspace_id > 33 and subworkspace_id not in (66, 88, 115, 166, 167, 168) and type_id = 4554 	and document not in ("12345678");'); // solos para usuarios Demo 1.0 a Demo 2.0
        $usuarios = $origen->select("SELECT document, email, username FROM users WHERE subworkspace_id IS NOT NULL AND type_id IN (select id from taxonomies t where t.group='user' and t.type='type' and t.code='client');");
        // Migrar cada usuario a la base de datos de destino
        foreach ($usuarios as $usuario) {
            // Verificar si el usuario ya existe en la base de datos de destino
            $existingUser = $destino
                ->table('master_usuarios')
                ->where('dni', $usuario->document)
                // ->orWhere('email', $usuario->email)
                ->first();
            info('Usuario existente: ' . $usuario->document . $usuario->email);
            if (!$existingUser) {
                if ($usuario->email == '') {
                    $destino->table('master_usuarios')->insert([
                    'dni' => $usuario->document,
                    'username' => $usuario->username,
                    // 'email' => 'sin_correo'.$correo_nulo++,
                    'email' => '',
                    'customer_id' => ENV('CUSTOMER_ID'),
                ]);
                info('Usuario migrado (sin-correo): ' . $usuario->document);
                } else {

                    $destino->table('master_usuarios')->insert([
                        'dni' => $usuario->document,
                        'username' => $usuario->username,
                        'email' => $usuario->email,
                        'customer_id' => ENV('CUSTOMER_ID'),
                    ]);
                    info('Usuario migrado (con-correo): ' . $usuario->document);
                }
            }
        }
        $this->info('Usuarios migrados con éxito.');
        return Command::SUCCESS;
    }
}
