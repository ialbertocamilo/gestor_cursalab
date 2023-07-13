<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomCRUD;
use Illuminate\Support\Facades\DB;


class UsuarioMaster extends Model
{
    use CustomCRUD;
    protected $connection = 'mysql_master';
    protected $table = 'master_usuarios';
    public $timestamps = false;
    protected $fillable = [
    	'dni','email','username','customer_id','created_at', 'updated_at', 'delete_at'
    ];

    protected function storeRequest($data, $user = null)
    {
        try {

            DB::beginTransaction();
            if ($user == null) {
                $user = new UsuarioMaster();
                $user->create($data);
            }else {
                $user->update($data);
            }
            info($user);
            $user->save();

            DB::commit();
        } catch (\Exception $e) {

            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }
}
