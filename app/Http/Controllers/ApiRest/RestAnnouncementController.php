<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestAnnouncementController extends Controller
{
    public function announcements()
    {
        $user = Auth::user();
        $anuncios = Announcement::getPublisheds($user->subworkspace?->criterion_value_id);

        return $this->successApp(['data' => $anuncios]);
    }
}
