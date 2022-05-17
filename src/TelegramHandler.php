<?php

namespace Opekunov\LaravelTelegramBot;

class TelegramHandler extends Telegram
{
    private array $updates = [];

    /**
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramRequestException
     */
    public function handleUpdates()
    {
        $this->updates = $this->getUpdates();
    }
}
