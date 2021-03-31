<?php
return [

    'bot-api' => [
        'token' => env('TELEGRAM_DEFAULT_BOT_TOKEN', ''),
        'base_uri' => env('TELEGRAM_BASE_URI', 'https://api.telegram.org')
    ],
    'tech' => [
        'admin_chat' => env('TELEGRAM_ADMIN_CHAT'),
        'moderator_chat' => env('TELEGRAM_MODERATOR_CHAT')
    ],
    'max_message_length' => env('MAX_MESSAGE_LENGTH', 400),

];
