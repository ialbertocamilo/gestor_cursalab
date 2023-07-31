<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class CampaignPostulates extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'summoned_id', 'user_id', 'sustent', 'state'];

    protected $casts = [
        'state' => 'boolean'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function summoned() {
        return $this->belongsTo(CampaignSummoneds::class, 'summoned_id');
    }

    protected function count_checks($request) {

        $accepteds = self::where('summoned_id', $request->summoned_id)
                         ->where('state', 1)->count();
        
        $rejecteds = self::where('summoned_id', $request->summoned_id)
                         ->where('state', 0)->count();

        return ['accepteds' => $accepteds, 'rejecteds' => $rejecteds];
    }

    protected function reset_sustents($summoned_id) {
        return  self::where('summoned_id', $summoned_id)
                    ->update(['state'=> NULL]);

    }
    
    protected function search($request) {
        $q = self::query();

        $q->where('summoned_id', $request->summoned_id)
          ->with('user:id,name,lastname,surname,fullname,document');

        return $q->paginate($request->paginate);
    }

    protected function storeStateSustents($request) {

        DB::beginTransaction();
        
        try {
            foreach($request->currSustents as $key => [ 'id' => $id_postulate,
                                                        'accepted' => $accepted]) {

                self::where('id', $id_postulate)
                     ->where('summoned_id', $request->summoned_id)
                     ->update(['state' => $accepted ? 1 : 0]);
            }
            
            DB::commit();
            return true; 

        } catch (Exception $e) {

            DB::rollBack();
            return false;

        }
    
    }
}
