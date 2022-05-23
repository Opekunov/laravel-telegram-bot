<?php

namespace Opekunov\LaravelTelegramBot;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramBadTokenException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramRequestException;

class Telegram
{
    /**
     * Constant for type Inline Query.
     */
    const INLINE_QUERY = 'inline_query';
    /**
     * Constant for type Callback Query.
     */
    const CALLBACK_QUERY = 'callback_query';
    /**
     * Constant for type Edited Message.
     */
    const EDITED_MESSAGE = 'edited_message';
    /**
     * Constant for type Reply.
     */
    const REPLY = 'reply';
    /**
     * Constant for type Message.
     */
    const MESSAGE = 'message';
    /**
     * Constant for type Photo.
     */
    const PHOTO = 'photo';
    /**
     * Constant for type Video.
     */
    const VIDEO = 'video';
    /**
     * Constant for type Audio.
     */
    const AUDIO = 'audio';
    /**
     * Constant for type Voice.
     */
    const VOICE = 'voice';
    /**
     * Constant for type animation.
     */
    const ANIMATION = 'animation';
    /**
     * Constant for type sticker.
     */
    const STICKER = 'sticker';
    /**
     * Constant for type Document.
     */
    const DOCUMENT = 'document';
    /**
     * Constant for type Location.
     */
    const LOCATION = 'location';
    /**
     * Constant for type Contact.
     */
    const CONTACT = 'contact';
    /**
     * Constant for type Channel Post.
     */
    const CHANNEL_POST = 'channel_post';

    private string $botToken = '';
    private string $botUsername = '';
    private int $botId;
    private string $baseApiUri = 'https://api.telegram.org';
    private array $updates = [];
    private int $timeout = 10;

    /**
     * Create a Telegram instance from the bot token
     *
     * @param  string  $botToken
     * @param  string|null  $baseApiUri  default is https://api.telegram.org
     * @param  string  $botUsername
     *
     * @throws TelegramException
     */
    public function __construct(string $botToken, ?string $baseApiUri = null, string $botUsername = '')
    {
        if ((is_string($baseApiUri) && empty($baseApiUri)) || empty($botToken)) {
            throw new TelegramException('API Uri or Bot Token not defined');
        }

        preg_match('/(\d+):[\w\-]+/', $botToken, $matches);
        if (!isset($matches[1])) {
            throw new TelegramException('Invalid Bot Token defined!');
        }

        $this->botId = (int)$matches[1];
        $this->botUsername = $botUsername;

        $this->baseApiUri = $baseApiUri ? trim($baseApiUri, '/') : $this->baseApiUri;
        $this->botToken = $botToken;
    }

    /**
     * Send api request
     *
     * @param  string  $endPoint  Метод телеграмма
     * @param  array  $data
     *
     * @return array
     * @throws TelegramBadTokenException
     * @throws TelegramRequestException
     */
    protected function sendRequest(string $endPoint, array $data): array
    {
        $uri = $this->baseApiUri.'/bot'.$this->botToken.'/'.trim($endPoint, '/');

        $client = new Client([
            'timeout' => $this->timeout
        ]);

        try {
            $response = $client->request('POST', $uri, ['form_params' => $data]);

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
