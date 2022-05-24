<?php

namespace Opekunov\LaravelTelegramBot;

use Opekunov\LaravelTelegramBot\Exceptions\TelegramRequestException;

/**
 * DEPRECATED
 * //TODO: REMOVE
 */
class _Telegram extends TelegramCore
{
    /**
     * Получить данные о боте
     *
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws TelegramRequestException
     */
    public function getMe(): array
    {
        return $this->sendRequest('getMe');
    }

    /**
     * Установка Webhook
     *
     * @param  string  $token  Bot token
     * @param  string  $url  HTTPS url to send updates to. Use an empty string to remove webhook integration
     * @param  int  $maxConnections  Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults to
     *     50. Use lower values to limit the load on your bot's server, and higher values to increase your bot's throughput.
     *
     * @return array
     * @throws TelegramRequestException
     * @throws Exceptions\TelegramTooManyRequestsException
     */
    public function setWebhook(string $token, string $url, int $maxConnections = 50): array
    {
        return $this->sendRequestWithBotToken($token, 'setWebhook', ['url' => $url, 'max_connections' => $maxConnections]);
    }

    /**
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws TelegramRequestException
     */
    public function getUpdates(int $offset = null, int $limit = 100): array
    {
        $data = ['limit' => $limit];
        if($offset) $data['offset'] = $offset;

        return $this->sendRequest('getUpdates', $data);
    }
}