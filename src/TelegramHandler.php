<?php

namespace Opekunov\LaravelTelegramBot;

class TelegramHandler extends Telegram
{
    private array $updates = [];

    private TelegramParser $parser;

    /**
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    public function handleUpdates()
    {
        $this->updates = $this->getUpdates();
    }

    public function handle(array $response): TelegramParser
    {
        return $this->parser = new TelegramParser($response);
    }
}
