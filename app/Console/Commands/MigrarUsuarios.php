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
    protected $signature = 'usuarios:migrar';

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
        // Conexión a la base de datos de origen
        $origen = DB::connection('mysql');

        // Conexión a la base de datos de destino
        $destino = DB::connection('mysql_master');

        // Obtener los usuarios de la base de datos de origen
        $usuarios = $origen->table('users')->get();

        // Migrar cada usuario a la base de datos de destino
        foreach ($usuarios as $usuario) {
            // Verificar si el usuario ya existe en la base de datos de destino
            $existingUser = $destino
                ->table('master_usuarios')
                ->where('dni', $usuario->document)
                ->orWhere('email', $usuario->email)
                ->first();

            if (!$existingUser) {
                $destino->table('master_usuarios')->insert([
                    'dni' => $usuario->document,
                    'username' => $usuario->username,
                    'email' => $usuario->email,
                    'customer_id' => ENV('CUSTOMER_ID'),
                    'created_at' => $usuario->created_at,
                    'updated_at' => $usuario->updated_at,
                    'delete_at' => $usuario->deleted_at,
                ]);
            }
        }
        $this->info('Usuarios migrados con éxito.');
        return Command::SUCCESS;
    }
}
