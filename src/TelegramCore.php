<?php


namespace Opekunov\LaravelTelegramBot;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramBadTokenException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramRequestException;

class TelegramCore
{
    protected string $_botToken;
    protected string $_baseUri;

    public function __construct(string $botToken, string $baseUri = 'https://api.telegram.org')
    {
        $this->_baseUri = $baseUri;
        $this->_botToken = $botToken;
    }

    /**
     * Отправка запроса боту из конфига
     *
     * @param  string  $endPoint  Метод телеграмма
     * @param  array  $data  Дата
     *
     * @throws TelegramRequestException
     * @throws TelegramBadTokenException
     */
    protected function sendRequest(string $endPoint, array $data = []): array
    {
        return $this->sendRequestWithBotToken($this->_botToken, $endPoint, $data);
    }

    /**
     * Отправка запроса
     *
     * @param  string  $botToken
     * @param  string  $endPoint  Метод телеграмма
     * @param  array  $data  Дата
     *
     * @return array
     * @throws TelegramRequestException
     * @throws TelegramBadTokenException
     */
    protected function sendRequestWithBotToken(string $botToken, string $endPoint, array $data = []): array
    {
        //todo: {"ok":false,"error_code":400,"description":"Bad Request: message text is empty"}

        $uri = $this->_baseUri.'/bot'.$botToken.'/'.$endPoint;

        $client = new Client([
            'timeout' => 10
        ]);

        try {
            $response = $client->request('POST', $uri, ['body' => json_encode($data)]);

            $decodedResponse = json_decode($response->getBody(), true);
            if ($decodedResponse === null) {
                $status = $response->getStatusCode();
                $body = $response->getBody();
                throw new TelegramRequestException("Can't encode JSON. Status: {$status}. Body: {$body}");
            }
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new TelegramBadTokenException('Bad token. Response body: '.$e->getResponse()->getBody());
            } else {
                throw new TelegramRequestException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        } catch (GuzzleException $e) {
            throw new TelegramRequestException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return is_array($decodedResponse['result']) ? $decodedResponse['result'] : $decodedResponse;
    }

}
