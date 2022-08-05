<?php

return [
    'date_format' => 'Y-m-d\TH:i:sZ',
    'requests' => [
        'meeting' => [
            // https://marketplace.zoom.us/docs/api-reference/zoom-api/methods/#operation/meetingCreate
            'create' => [
                "duration" => "DURACION DEL EVENTO",
                "settings" => [
                    "host_video" => false,
                    "participant_video" => false,
                    "meeting_authentication" => false,
                    "registrants_confirmation_email" => false,
                    "registrants_email_notification" => false,
//                    "watermark" => true,
                    "approval_type" => 0,
                    "registration_type" => 2,
                ],
                "start_time" => "Y-m-d\TH:i:s",
                "topic" => "TITULO DEL EVENTO",
                "agenda" => "AGENDA DEL EVENTO",
                "timezone" => "America/Lima",
                "type" => 2,
                "waiting_room" => true,
                "password" => "",
            ]
        ]
    ]
];
