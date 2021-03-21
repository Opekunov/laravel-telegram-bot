<?php
return [

    'bot-api' => [
        'token' => env('TELEGRAM_GIFT_BOT_TOKEN', null),
        'base_uri' => env('TELEGRAM_BASE_URI', null)
    ],
    'tech' => [
        'admin_chat' => env('TELEGRAM_ADMIN_CHAT'),
        'moderator_chat' => env('TELEGRAM_MODERATOR_CHAT')
    ],
    'max_message_length' => env('MAX_MESSAGE_LENGTH', 400),
    'bot_name' => env('TELEGRAM_BOT_NAME', 'SashaCarloBot'),
    'event_bot_name' => env('TELEGRAM_EVENT_BOT_NAME', 'MakarCarloBot'),
    'triggers' => [
        'words' => [
            "спасибо" => 4, "спасиб" => 4, 'спс' => 4, 'спасибор' => 4, 'thx' => 4,
            'спосибо' => 4, 'spasib' => 4, 'spasibo' => 4, 'мерси' => 4, 'спасибки' => 4,
            "лайк" => 5, 'like' => 5, 'лайкаю' => 5
        ],
        'emojies' => [
            '👍' => 4, '❤️' => 5
        ]
    ]

];
