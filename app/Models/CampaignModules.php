<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{ Model, SoftDeletes };

class CampaignModules extends Model
{
    use SoftDeletes;
    
    protected $table = 'campaign_modules';
    protected $fillable = ['id', 'campaign_id', 'module_id'];

    protected function saveModules($campaign, $campaign_modules) {

        $modules_out = [];
        $modules_exist = $campaign->modules->isEmpty();
        
        if(!$modules_exist) {
            $modules_out = Arr::pluck($campaign->modules, 'module_id');
        }

        $modules_in = Arr::pluck($campaign_modules, 'id');
        if($modules_out == $modules_in) return; 

        $proccess_modules = Arr::map($modules_in, function($module_id) use ($campaign) {
            return ['campaign_id' => $campaign->id, 'module_id' => $module_id ];
        });

        DB::beginTransaction();

        try {
            // elimina los modulos
            if(!$modules_exist) {
               self::where('campaign_id', $campaign->id)->delete();
            }
            $res = self::insert($proccess_modules);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
