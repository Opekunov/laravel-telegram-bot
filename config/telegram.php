<?php

return [
    'token'    => env('TELEGRAM_BOT_TOKEN', ''), // bot token
    'name'     => env('TELEGRAM_BOT_NAME', ''), // bot username. without @
    'base_uri' => env('TELEGRAM_BASE_URI', 'https://api.telegram.org'), //base uri

    // Limiter  class. Default limiter use cache driver.
    'limiter' => \Opekunov\LaravelTelegramBot\Limiter\Limiter::class,
];
