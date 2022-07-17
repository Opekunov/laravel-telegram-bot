<?php

namespace Opekunov\LaravelTelegramBot\Exceptions;

class TelegramRequestException extends TelegramException
{
    public ?int $chatId;

    public function __construct(?int $chatId, $message = "", $code = 0, $previous = null)
    {
        $this->chatId = $chatId;
        parent::__construct($message, $code, $previous);
    }
}
