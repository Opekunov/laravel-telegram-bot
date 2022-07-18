<?php

namespace Opekunov\LaravelTelegramBot\Exceptions;

class InvalidBotTokenException extends TelegramException
{
    public function __construct()
    {
        parent::__construct('Invalid bot token');
    }
}
