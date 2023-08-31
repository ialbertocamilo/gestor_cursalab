<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserNotification extends BaseModel
{

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

    protected $table = 'user_notifications';

    protected $fillable = [
        'path',
        'content',
        'user_id',
        'is_read',
        'is_visible'
    ];

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id', 'id');
    }

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
        $workspaceId, $userIds, $notficationType, $contentValues, $path
    ): void
    {
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

        // Get users who have already unread notifications

        $notifiedUsers = self::query()
            ->where('path', $path)
            ->where('workspace_id', $workspaceId)
            ->where('content', $content)
            ->where('is_visible', 1)
            ->select('user_id')
            ->groupBy('user_id')
            ->get()
            ->pluck('user_id')
            ->toArray();

        // Create notifications for each user

        $values = [];

        foreach ($userIds as $userId) {

            // Exclude user if it has been already notified

            if (!in_array($userId, $notifiedUsers)) {
                $createdAt = now();
                $values[] = "($workspaceId, $taxonomy->id, '$path', '$content', $userId, '$createdAt')";
            }
        }

        // Generate SQL script to insert all rows in one statement

        if (count($values)) {
            info('notifications start:', now());
            $statement = "
                insert into
                    user_notifications (
                       workspace_id, type_id, path, content, user_id, created_at)
                    values " . implode(',', $values);
            DB::select(DB::raw($statement));
            info('notifications finished:', now());
        }
    }

    /**
     * Load user notifications grouped by date
     */
    public static function loadUserNotifications ($userId): array
    {

        $notications = UserNotification::with('type')
            ->where('user_id', $userId)
            ->where('is_visible', 1)
            ->orderBy('created_at', 'desc')
            ->select([
                'id', 'user_id', 'workspace_id',
                'path', 'content', 'is_visible',
                'is_read', 'type_id',
                'created_at',
                DB::raw('date(created_at) created_date')
            ])
            //->whereDate('created_at', '>', now()->subDays(5))
            ->get();

        // Group notifications by date

        Carbon::setLocale('es');

        foreach ($notications as &$notication) {
            $date = $notication->created_at;
            $notication->created_date =
                $date->day . ' de ' .  $date->monthName . ' ' . $date->year;

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
}
