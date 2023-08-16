<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{ Model, SoftDeletes };

class CampaignBadges extends Model
{
    use SoftDeletes;

    protected $table = 'campaign_badges';
    protected $fillable = ['id', 'campaign_id', 'file_badge', 'position', 'state'];

    protected function saveBadge($file_badge, $position, $campaign) {

        $badge_data['campaign_id'] = $campaign->id;
        $badge_data['file_badge'] = $file_badge;
        $badge_data['position'] = $position;

        $badge_data = Media::requestUploadFileOnly($badge_data, 'badge');

        self::create($badge_data);
    }

    protected function updateBadge($file_badge, $position, $campaign, $delete = false) {

        $badge_data['file_badge'] = $file_badge;
        $badge_data['position'] = $position;

        if($delete) {
            self::where('campaign_id', $campaign->id)
                ->where('position', $position)->delete();
        } else {

            $badge_exist = self::where('campaign_id', $campaign->id)
                                ->where('position', $position)->first();
            // exist badge
            if($badge_exist) {
                $badge_data = Media::requestUploadFileOnly($badge_data, 'badge');
                $badge_exist->update($badge_data);
            }else {
                self::saveBadge($file_badge, $position, $campaign);
            }
        }
    }
}
