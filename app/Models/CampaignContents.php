<?php

namespace App\Models;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Arr;

class CampaignContents extends Model
{
    use SoftDeletes;
    
    protected $table = 'campaign_contents';
    protected $fillable = ['id', 'campaign_id', 'file_media', 'title', 'description', 'linked', 'download', 'state'];

    protected function saveContents($contents, $campaign) {

        $proccess_contents = Arr::map($contents, function($content) use ($campaign) {

            if ($content['linked'] == 'null' || $content['linked'] == NULL) {
                $content['linked'] = NULL;
            } else {
                if (str_contains($content['linked'], 'vimeo.com/')) {
                    $content['linked'] = extractVimeoVideoCode($content['linked']);
                }

                if (str_contains($content['linked'], 'youtube.com/') || str_contains($content['linked'], 'youtu.be/')) {
                    $content['linked'] = extractYoutubeVideoCode($content['linked']);
                } 
            }

            $content = Media::requestUploadFileOnly($content, 'media');
            if ($content['file_media'] == 'null' || $content['file_media'] == NULL) {
                $content['file_media'] = NULL;
            }

            $content['campaign_id'] = $campaign->id;
            $content['state'] = ($content['state'] == 'true' || $content['state'] == true) ? true : false;

            return $content;
        });

        DB::beginTransaction();

        try {
            // elimina los modulos
            if(!($campaign->contents->isEmpty())) {
               self::where('campaign_id', $campaign->id)->delete();
            }
            self::insert($proccess_contents);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

}
