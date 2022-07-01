<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SourceMultimarca extends Model
{
    use SoftDeletes;

    protected $table = 'source_multimarca';

    public $timestamps = false;

    protected $fillable = [
    	'type',
        'customer_id',
        'code',
        'type_id'
    ];

    protected $connection = 'mysql2';

    public static function insertSource($code,$type,$type_id)
    {
        $url = url('/');
        $customer = DB::connection('mysql2')->table('customers')->where('route',$url)->first();
        if($customer){
            self::insert([
                'code'=>$code,
                'customer_id'=>$customer->id,
                'type'=>$type,
                'type_id'=>$type_id
            ]);
        }else{
            Error::storeAndNotificateDefault(
                'No se encuentra al cliente: '.$url,
                'Aulas Virtuales',
                'Crear Reunión');
        }
    }

    public static function destroySource($type=null,$type_id=null,$code=null)
    {
        $source = new SourceMultimarca();
        ($type) && $source=$source->where('type',$type);
        ($type_id) && $source=$source->where('type_id',$type_id);
        ($code) && $source=$source->where('code',$code);
        $source = $source->first();
        if($source){
            $source->delete();
        }
    }
}
