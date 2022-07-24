<?php

namespace Opekunov\LaravelTelegramBot;

use Illuminate\Support\ServiceProvider;

class LaravelTelegramBotServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/telegram.php.php', 'telegram');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/telegram.php' => config_path('telegram.php')
        ]);
    }
}
