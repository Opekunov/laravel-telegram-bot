<?php

namespace Opekunov\LaravelTelegramBot;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramRequestException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramTooManyRequestsException;

class Telegram
{
    protected string $botToken = '';
    protected string $botUsername = '';
    protected int $botId;
    protected string $baseApiUri = 'https://api.telegram.org';
    private array $updates = [];
    private int $timeout = 10;

    /**
     * Create a Telegram instance from the bot token
     *
     * @param  string|null  $botToken
     * @param  string|null  $baseApiUri  default is https://api.telegram.org
     * @param  string  $botUsername
     *
     * @throws TelegramException
     */
    public function __construct(string $botToken = null, ?string $baseApiUri = null, string $botUsername = '')
    {
        $baseApiUri = $baseApiUri ?? $this->baseApiUri;

        if (function_exists('config')) {
            $botToken = $botToken ?? config('telegram.token');
            $baseApiUri = $baseApiUri ?? config('telegram.base_uri') ?? $this->baseApiUri;
            $botUsername = empty($botUsername) ? config('telegram.name') : $botUsername;
            $this->timeout = config('telegram.timeout') ?? $this->timeout;
        }

        if (!filter_var($baseApiUri, FILTER_VALIDATE_URL)) {
            throw new TelegramException('API Uri Bad or not defined');
        }

        preg_match('/(\d+):[\w\-]+/', $botToken, $matches);
        if (empty($botToken) || !isset($matches[1])) {
            throw new TelegramException('Invalid Bot Token defined');
        }

        $this->botId = (int)$matches[1];
        $this->botUsername = $botUsername;

        $this->baseApiUri = $baseApiUri ? trim($baseApiUri, '/') : $this->baseApiUri;
        $this->botToken = $botToken;
    }

    /**
     * A simple method for testing your bot's authentication token. Requires no parameters.
     * Returns basic information about the bot in form of a User object.
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws GuzzleException
     * @throws TelegramRequestException
     * @throws TelegramTooManyRequestsException
     */
    public function getMe(): array
    {
        return $this->sendRequest('getMe', null);
    }

    /**
     * Send API request
     *
     * @param  string  $endPoint
     * @param  array  $data
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws GuzzleException
     * @throws TelegramRequestException
     * @throws TelegramTooManyRequestsException
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
        } catch (\Exception $exception) {
            (new TelegramExceptionHandler())->handle($exception, @$data['chat_id']);
        }
        return is_array($decodedResponse['result']) ? $decodedResponse['result'] : $decodedResponse;
    }

    /**
     * Use this method to specify a url and receive incoming updates via an outgoing webhook. Whenever there is an update for the bot, we will send
     * an HTTPS POST request to the specified url, containing a JSON-serialized Update. In case of an unsuccessful request, we will give up after a
     * reasonable amount of attempts. Returns True on success.
     *
     *
     * @param  string  $url  HTTPS url to send updates to. Use an empty string to remove webhook integration
     * @param  int  $maxConnections  Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults to
     *     50. Use lower values to limit the load on your bot's server, and higher values to increase your bot's throughput.
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws GuzzleException
     * @throws TelegramRequestException
     * @throws TelegramTooManyRequestsException
     * @see https://core.telegram.org/bots/api#setwebhook
     */
    public function setWebhook(string $url, int $maxConnections = 40): array
    {
        return $this->sendRequest(
            'setWebhook',
            [
                'url' => $url,
                'max_connections' => $maxConnections
            ]
        );
    }

    /**
     * Use this method to remove webhook integration if you decide to switch back
     * to getUpdates. Returns True on success.
     *
     * @param  bool  $dropPendingUpdates  Pass True to drop all pending updates
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws GuzzleException
     * @throws TelegramRequestException
     * @throws TelegramTooManyRequestsException
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): array
    {
        return $this->sendRequest('deleteWebhook', ['drop_pending_updates' => $dropPendingUpdates]);
    }

    /// Receive incoming messages using polling

    /**
     * Use this method to receive incoming updates using long polling.
     *
     * @param $offset Integer Identifier of the first update to be returned. Must be greater by one than the highest among the identifiers of
     *     previously received updates. By default, updates starting with the earliest unconfirmed update are returned. An update is considered
     *     confirmed as soon as getUpdates is called with an offset higher than its update_id.
     * @param $limit Integer Limits the number of updates to be retrieved. Values between 1—100 are accepted. Defaults to 100
     * @param $timeout Integer Timeout in seconds for long polling. Defaults to 0, i.e. usual short polling
     * @param $update Boolean If true updates the pending message list to the last update received. Default to true.
     *
     * @return array the updates as Array.
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws GuzzleException
     * @throws TelegramRequestException
     * @throws TelegramTooManyRequestsException
     * @see https://core.telegram.org/bots/api#getupdates
     */
    public function getUpdates(int $offset = 0, int $limit = 100, int $timeout = 0, bool $update = true): array
    {
        $content = ['offset' => $offset, 'limit' => $limit, 'timeout' => $timeout];
        $this->updates = $this->sendRequest('getUpdates', $content);
        if ($update) {
            if (array_key_exists('result', $this->updates) && is_array($this->updates['result']) && count(
                    $this->updates['result']
                ) >= 1) { //for CLI working.
                $lastElementId = $this->updates['result'][count($this->updates['result']) - 1]['update_id'] + 1;
                $content = ['offset' => $lastElementId, 'limit' => 1, 'timeout' => $timeout];
                $this->sendRequest('getUpdates', $content);
            }
        }

        return $this->updates;
    }

    /** Use this method to use the bultin function like Text() or Username() on a specific update.
     *
     * @param $update int The index of the update in the updates array.
     *
     * @throws TelegramException
     */
    public function serveUpdate(int $update): TelegramRequest
    {
        if (!$this->updateCount()) {
            throw new TelegramException("Updates is empty");
        }
        if (!isset($this->updates['result'][$update])) {
            throw new TelegramException("$update doesn't exist in updates");
        }
        return new TelegramRequest($this->updates['result'][$update]);
    }

    /**
     * Get the number of updates
     *
     * @return int
     */
    public function updateCount(): int
    {
        return count($this->updates['result']);
    }
}
