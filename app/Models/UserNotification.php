<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends BaseModel
{

    protected $table = 'user_notifications';


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
}
