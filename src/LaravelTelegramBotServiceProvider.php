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
        include __DIR__.'/config/telegram.php';
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/telegram.php' => config_path('telegram.php'),
        ]);
    }
}
