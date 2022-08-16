<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestQuizController extends Controller
{
    public function announcements()
    {
        $user = Auth::user();
        $anuncios = Announcement::getPublisheds($user->workspace?->id);

        return $this->successApp(['data' => $anuncios]);
    }
}
