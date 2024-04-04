<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Schema\Blueprint;

class UserNotification extends Model
{
    protected $connection = 'mongodb';

    // Notifications types

    public const NEW_COURSE = 'new-course';
    public const NEW_COURSES = 'new-courses';
    public const NEW_CERTIFICATE = 'new-certificate';
    public const PASSWORD_RESET  = 'password-reset';
    public const PASSWORD_UPDATE = 'password-update';
    public const EMAIL_UPDATE = 'email-update';
    public const TICKET_SOLVED = 'ticket-solved';
    public const NEW_BENEFIFT = 'new-benefit';
    public const NEW_DOCUMENT = 'new-document';
    public const NEW_ANNOUNCEMENT = 'new-announcement';
    public const NEW_VIDEO = 'new-video';
    public const FROM_PUSH = 'from-push';
    public const NEW_MEETING = 'new-meeting';
    public const COURSE_ATTEMPTS_RESET = 'course-attempts-reset';
    public const TOPIC_ATTEMPTS_RESET = 'topic-attempts-reset';

    protected $collection = 'user_notifications';

    protected $fillable = [
        'path',
        'content',
        'user_id',
        'is_read',
        'is_visible',
        'workspace_id',
        'type_id'
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

    /**
     * Create notifications for users who have not been notified yet
     *
     * @param $workspaceId
     * @param $userIds
     * @param $notficationType
     * @param $contentValues
     * @param $path
     * @return void
     */
    public static function createNotifications(
        $workspaceId, $userIds, $notficationType, $contentValues, $path = null
    ): void
    {

        // Generate message content replacing values in message template

        $taxonomy = Taxonomy::query()
            ->where('group', 'user-notifications')
            ->where('code', $notficationType)
            ->first();
        if (!$taxonomy) return;
        $type = $taxonomy->type;

        $content = config("notifications.$type.$notficationType");
        foreach ($contentValues as $key => $value) {
            // Replace value in template
            $content = str_ireplace('{' . $key . '}', $value, $content);
        }

        // Create notifications for each user

        $notifiedUsersIds = self::query()
            ->where('workspace_id', $workspaceId)
            ->where('type_id', $taxonomy->id)
            ->where('is_visible', 1)
            ->where('content', $content)
            ->where('path', $path)
            ->get()
            ->pluck('user_id')
            ->toArray();

        $values = [];
        foreach ($userIds as $userId) {

            if (!in_array($userId, $notifiedUsersIds)) {
                $values[] = [
                    'workspace_id' => $workspaceId,
                    'user_id' => $userId,
                    'type_id' => $taxonomy->id,
                    'path' => $path,
                    'content' => $content,
                    'is_visible' => 1,
                    'is_read' => 0,
                    'created_at' => new \MongoDB\BSON\UTCDateTime(now()),
                    'updated_at' => null
                ];
            }
        }

        if ( count($values) ) {
            UserNotification::insert($values);
        }
    }

    /**
     * Load user notifications grouped by date
     */
    public static function loadUserNotifications ($user): array
    {
        $workspace_id = $user->subworkspace?->parent_id;
        $userId = $user->id;
        $notications = UserNotification::query()
            ->where('user_id', $userId)
            ->where('workspace_id', $workspace_id)
            ->where('is_visible', 1)
            ->orderBy('created_at', 'desc')
            ->select([
                'id', 'user_id', 'workspace_id',
                'path', 'content', 'is_visible',
                'is_read', 'type_id',
                'created_at'
            ])
            ->where('created_at', '>', now()->subDays(5))
            ->get();

        // Remove duplicates and add additional data

        Carbon::setLocale('es');

        $alreadyAdded = [];
        $_notifications = [];
        foreach ($notications as $notication) {

            $identifier =  self::generateUniqueIdentifier($notication, $userId);

            if (!in_array($identifier, $alreadyAdded)) {
                $date = $notication->created_at;
                $notication->id = $notication->_id;
                $notication->created_date =
                    $date->day . ' de ' .  $date->monthName . ' ' . $date->year;
                $notication->type = Taxonomy::find($notication->type_id);

                $_notifications[] = $notication;
                $alreadyAdded[] = $identifier;
            }
        }

        // Create groups

        $responseNotifications = [];
        $groupedNotifications = collect($_notifications)
            ->groupBy('created_date')
            ->toArray();

        foreach ($groupedNotifications as $groupName => $notifications) {
            $responseNotifications[] = [
                'date' => $groupName,
                'notifications' => $notifications
            ];
        }

        return $responseNotifications;
    }

    /**
     * Generate notification unique identifier
     * @param $notification
     * @param $userId
     * @return string
     */
    public static function generateUniqueIdentifier($notification, $userId): string
    {
        return sha1($notification->workspace_id .
            $notification->type_id .
            $userId .
            $notification->is_visible .
            $notification->content .
            $notification->path);
    }
}
