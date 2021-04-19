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

    /**
     * Установка Webhook
     * @param string $token Bot token
     * @param string $url HTTPS url to send updates to. Use an empty string to remove webhook integration
     * @param int $maxConnections Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults to 50. Use lower values to limit the load on your bot's server, and higher values to increase your bot's throughput.
     * @return array
     */
    public function setWebhook(string $token, string $url, int $maxConnections = 50): array
    {
        return $this->sendRequestWithBotToken($token, 'setWebhook', ['url' => $url, 'max_connections' => $maxConnections])->json();
    }
}
