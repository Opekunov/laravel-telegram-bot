<?php


namespace Opekunov\LaravelTelegramBot\Telegram;


use Illuminate\Support\Facades\Http;

class TelegramCore
{
    protected $_botUri;
    protected $_baseUri;

    public function __construct()
    {
        $this->_baseUri = config('telegram.bot-api.base_uri');
        $this->_botUri = config('telegram.bot-api.token');
    }

    /**
     * Отправка запроса боту из конфига
     * @param string $endPoint Метод телеграмма
     * @param null $data Дата
     * @return \Illuminate\Http\Client\Response
     */
    protected function sendRequest(string $endPoint, $data = null)
    {
        return $this->sendRequestWithBotToken($this->_botUri, $endPoint, $data);
    }

    /**
     * Отправка запроса
     * @param string $botToken
     * @param string $endPoint Метод телеграмма
     * @param null $data Дата
     * @return \Illuminate\Http\Client\Response
     */
    protected function sendRequestWithBotToken(string $botToken, string $endPoint, $data = null)
    {
        $uri = $this->_baseUri . '/bot' . $botToken . '/' . $endPoint;
        return Http::post($uri, $data);
    }

}
