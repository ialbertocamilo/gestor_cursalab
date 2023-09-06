<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Schema\Blueprint;

class UserNotification extends Model
{
    protected $connection = 'mongodb';
    protected $primaryKey = 'id';

    // Notifications types

    public const NEW_COURSE = 'new-course';
    public const NEW_CERTIFICATE = 'new-certificate';
    public const PASSWORD_RESET  = 'password-reset';
    public const PASSWORD_UPDATE = 'password-update';
    public const EMAIL_UPDATE = 'email-update';
    public const TICKET_SOLVED = 'ticket-solved';
    public const NEW_BENEFIFT = 'new-benefit';
    public const NEW_DOCUMENT = 'new-document';
    public const NEW_ANNOUNCEMENT = 'new-announcement';

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

        if (UserNotification::count() === 0) {
            self::addUniqueIndexToCollection();
        }

        // Generate message content replacing values in message template

        $taxonomy = Taxonomy::query()
            ->where('group', 'user-notifications')
            ->where('code', $notficationType)
            ->first();
        $type = $taxonomy->type;


        $content = config("notifications.$type.$notficationType");
        foreach ($contentValues as $key => $value) {
            // Replace value in template
            $content = str_ireplace('{' . $key . '}', $value, $content);
        }

        // Create notifications for each user

        $values = [];
        foreach ($userIds as $userId) {

            $notification = self::query()
                ->where('workspace_id', $workspaceId)
                ->where('type_id', $taxonomy->id)
                ->where('is_visible', 1)
                ->where('user_id', $userId)
                ->select()->first();

            if (!$notification) {
                UserNotification::create([
                    'workspace_id' => $workspaceId,
                    'user_id' => $userId,
                    'type_id' => $taxonomy->id,
                    'path' => $path,
                    'content' => $content,
                    'is_visible' => 1,
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => null
                ]);
            }
        }
    }

    /**
     * Load user notifications grouped by date
     */
    public static function loadUserNotifications ($userId): array
    {

        $notications = UserNotification::query()
            ->where('user_id', $userId)
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

        // Group notifications by date

        Carbon::setLocale('es');

        foreach ($notications as &$notication) {
            $date = $notication->created_at;
            $notication->created_date =
                $date->day . ' de ' .  $date->monthName . ' ' . $date->year;
            $notication->type = Taxonomy::find($notication->type_id);

        }

        $responseNotifications = [];
        $groupedNotifications = $notications->groupBy('created_date')->toArray();
        foreach ($groupedNotifications as $groupName => $notifications) {
            $responseNotifications[] = [
                'date' => $groupName,
                'notifications' => $notifications
            ];
        }

        return $responseNotifications;
    }

    /**
     * Add unique index to avoid duplicates
     * @return void
     */
    public static function addUniqueIndexToCollection() {

        Schema::connection('mongodb')
            ->table('user_notifications', function (Blueprint $collection) {
                $collection->unique([
                    'workspace_id', 'is_visible', 'user_id', 'type_id'
                ]);
        });
    }
}
