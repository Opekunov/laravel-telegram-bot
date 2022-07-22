<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot settings
    |--------------------------------------------------------------------------
    |
    | You can create your bot with @BotFather in telegram.
    | After copy and paste token and bot_name (is bot username, not Display name) here
    | If you want use your api endpoint, change TELEGRAM_BASE_URI in your .env file
    |
    */
    'token'        => env('TELEGRAM_BOT_TOKEN', ''), // bot token
    'name'         => env('TELEGRAM_BOT_NAME', ''), // bot username. without @
    'base_uri'     => env('TELEGRAM_BASE_URI', 'https://api.telegram.org'), //base uri

    /*
    |--------------------------------------------------------------------------
    | Limiter  class
    |--------------------------------------------------------------------------
    | Telegram has broadcasting limits.
    | For example: You cannot send more than 20 messages per minute to different groups and channels
    | This limiter help you control sending.
    |
    | You can create your own Limiter implements of Opekunov\LaravelTelegramBot\Limiter\Contracts\LimiterContract.
    | For example, you can make a Limiter that uses a database as a repository.
    |
    */
    'use_limiter'  => env('TELEGRAM_USE_LIMITER', true),
    'limiter'      => \Opekunov\LaravelTelegramBot\Limiter\Limiter::class,
    /*
      When limit reached package use driver for waiting.
      Available drivers:
      'job': This driver creates jobs in queues with delay. Recommended
      'sleep': This driver use usleep() function for delay
    */
    'waiting_type' => env('TELEGRAM_LIMITER_WAITING_DRIVER', 'job'),
];
