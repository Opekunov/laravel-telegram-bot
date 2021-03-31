<?php

namespace Opekunov\LaravelTelegramBot;

use Illuminate\Support\Str;

class Telegram extends TelegramCore
{
    /**
     * Получить данные о боте
     * @param $token
     * @return array
     */
    public function getMe($token): array
    {
        return $this->sendRequestWithBotToken($token, 'getMe')->json();
    }
}
