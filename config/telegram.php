<?php

return [
    'token'    => env('TELEGRAM_BOT_TOKEN', ''), // bot token
    'name'    => env('TELEGRAM_BOT_NAME', ''), // bot username. without @
    'base_uri' => env('TELEGRAM_BASE_URI', 'https://api.telegram.org') //base uri
];
