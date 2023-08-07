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
            if (is_null($user)) {
                $user = new UsuarioMaster();
                $user->fill($data);
                // info('create_master', [$user]);

            }else {
                $user->update($data);
                // info('update_master', [$user]);

            }
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
