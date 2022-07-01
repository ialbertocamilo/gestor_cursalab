<?php

namespace App\Http\Controllers;

// use Vimeo\Vimeo;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Vimeo\Laravel\Facades\Vimeo;

class VimeoApi extends Controller
{
    public function crearEventoEnVivo()
    {
        $data = [
            'automatically_title_stream' => false,
            'chat_enabled' => true,
            'embed' => (object) [
                'autoplay' => false,
                'color' => '#000000',
                'logos' => [
                    'custom' => [
                        'active' => false,
                        'link' => '',
                        'sticky' => false
                    ],
                    'vimeo' => true,
                ],
                'loop' => false,
                'playlist' => true,
                'schedule' => false,
                'use_color' => true
            ],
            'playlist_sort' => 'likes',
            'schedule' => (object) [
                'daily_time' => '',
                'start_time' => '',
                'type' => 'single',
                'weekdays' => []
            ],
            'stream_description' => 'Test JC 1',
            'stream_embed' => [
                'embed' => 'private'
            ],
            'stream_password' => 'abcd1234',
            'stream_privacy' => (object) [
                'view' => 'password',
            ],
            'stream_title' => 'Test JC 1',
            'time_zone' => 'GMT-5',
            'title' => 'Test JC 1'
        ];
        $response = Vimeo::request('/me/live_events', $data, 'POST');
        print_r($response);
    }
}
