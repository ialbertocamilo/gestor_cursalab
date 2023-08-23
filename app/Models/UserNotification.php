<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends BaseModel
{

    protected $table = 'user_notifications';

    protected $fillable = [
        'model_id',
        'model_type',
        'content',
        'user_id',
        'is_read',
        'is_visible'
    ];


    public function markAsRead() {
        $this->is_read = 1;
        $this->save();
    }

    /**
     * When hiding a notification, its status
     * should also be updated to "read"
     * @return void
     */
    public function hide() {
        $this->is_visible = 0;
        $this->is_read = 1;
        $this->save();
    }

    public static function createNotifications(
        $userIds, $content, $modelType, $modelId
    ) {

        // Get users who have already unread notifications

        $notifiedUsers = self::query()
            ->where('model_id', $modelId)
            ->where('model_type', $modelType)
            ->where('content', $content)
            ->where('is_read', 0)
            ->get()
            ->pluck('user_id')
            ->toArray();

        // Create notifications for each user

        foreach ($userIds as $userId) {

            // Exclude user if it has been already notified

            if (!in_array($userId, $notifiedUsers)) {
                self::create([
                    'model_id' => $modelId,
                    'model_type' => $modelType,
                    'content' => $content,
                    'user_id' => $userId
                ]);
            }
        }
    }
}
