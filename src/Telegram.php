<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opekunov\LaravelTelegramBot;

defined('TB_BASE_PATH') || define('TB_BASE_PATH', __DIR__);
defined('TB_BASE_COMMANDS_PATH') || define('TB_BASE_COMMANDS_PATH', TB_BASE_PATH . '/Commands');

use Exception;
use InvalidArgumentException;
use Opekunov\LaravelTelegramBot\Commands\AdminCommand;
use Opekunov\LaravelTelegramBot\Commands\Command;
use Opekunov\LaravelTelegramBot\Commands\SystemCommand;
use Opekunov\LaravelTelegramBot\Commands\UserCommand;
use Opekunov\LaravelTelegramBot\Entities\Chat;
use Opekunov\LaravelTelegramBot\Entities\ServerResponse;
use Opekunov\LaravelTelegramBot\Entities\Update;
use Opekunov\LaravelTelegramBot\Entities\User;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Telegram
{
    /**
     * Version
     *
     * @var string
     */
    protected string $version = '0.1.0';

    /**
     * Telegram API key
     *
     * @var string
     */
    protected string $api_key = '';

    /**
     * Telegram Bot username
     *
     * @var string
     */
    protected string $botUsername = '';

    /**
     * Telegram Bot id
     *
     * @var int
     */
    protected int $botId = 0;

    /**
     * Raw request data (json) for webhook methods
     *
     * @var string
     */
    protected string $input = '';

    /**
     * Current Update object
     *
     * @var Update
     */
    protected Update $update;

    /**
     * Upload path
     *
     * @var string
     */
    protected string $uploadPath = '';

    /**
     * Download path
     *
     * @var string
     */
    protected string $downloadPath = '';

    /**
     * Admins list
     *
     * @var array
     */
    protected array $adminsList = [];

    /**
     * Is running getUpdates without DB enabled
     *
     * @var bool
     */
    protected bool $getupdatesWithoutDatabase = false;

    /**
     * Last update ID
     * Only used when running getUpdates without a database
     *
     * @var int
     */
    protected int $lastUpdateId;

    /**
     * The command to be executed when there's a new message update and nothing more suitable is found
     */
    public const GENERIC_MESSAGE_COMMAND = 'genericmessage';

    /**
     * The command to be executed by default (when no other relevant commands are applicable)
     */
    public const GENERIC_COMMAND = 'generic';

    /**
     * Update filter method
     *
     * @var callable
     */
    protected $updateFilter;

    /**
     * Telegram constructor.
     *
     * @param string $api_key
     * @param string $bot_username
     *
     * @throws TelegramException
     */
    public function __construct(string $api_key, string $bot_username = '')
    {
        if (empty($api_key)) {
            throw new TelegramException('API KEY not defined!');
        }
        preg_match('/(\d+):[\w\-]+/', $api_key, $matches);
        if (!isset($matches[1])) {
            throw new TelegramException('Invalid API KEY defined!');
        }
        $this->botId  = (int)$matches[1];
        $this->api_key = $api_key;

        $this->botUsername = $bot_username;

        Request::initialize($this);
    }

    /**
     * Get namespace from php file by src path
     *
     * @param string $src (absolute path to file)
     *
     * @return string|null ("Longman\TelegramBot\Commands\SystemCommands" for example)
     */
    protected function getFileNamespace(string $src): ?string
    {
        $content = file_get_contents($src);
        if (preg_match('#^\s*namespace\s+(.+?);#m', $content, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Set custom input string for debug purposes
     *
     * @param string $input (json format)
     *
     * @return Telegram
     */
    public function setCustomInput(string $input): Telegram
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get custom input string for debug purposes
     *
     * @return string
     */
    public function getCustomInput(): string
    {
        return $this->input;
    }

    /**
     * Set custom upload path
     *
     * @param string $path Custom upload path
     *
     * @return Telegram
     */
    public function setUploadPath(string $path): Telegram
    {
        $this->uploadPath = $path;

        return $this;
    }

    /**
     * Get custom upload path
     *
     * @return string
     */
    public function getUploadPath(): string
    {
        return $this->uploadPath;
    }

    /**
     * Set custom download path
     *
     * @param string $path Custom download path
     *
     * @return Telegram
     */
    public function setDownloadPath(string $path): Telegram
    {
        $this->downloadPath = $path;

        return $this;
    }

    /**
     * Get custom download path
     *
     * @return string
     */
    public function getDownloadPath(): string
    {
        return $this->downloadPath;
    }

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->api_key;
    }

    /**
     * Get Bot name
     *
     * @return string
     */
    public function getBotUsername(): string
    {
        return $this->botUsername;
    }

    /**
     * Get Bot Id
     *
     * @return int
     */
    public function getBotId(): int
    {
        return $this->botId;
    }

    /**
     * Get Version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set Webhook for bot
     *
     * @param string $url
     * @param array  $data Optional parameters.
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function setWebhook(string $url, array $data = []): ServerResponse
    {
        if ($url === '') {
            throw new TelegramException('Hook url is empty!');
        }

        $data        = array_intersect_key($data, array_flip([
            'certificate',
            'ip_address',
            'max_connections',
            'allowed_updates',
            'drop_pending_updates',
            'secret_token',
        ]));
        $data['url'] = $url;

        // If the certificate is passed as a path, encode and add the file to the data array.
        if (!empty($data['certificate']) && is_string($data['certificate'])) {
            $data['certificate'] = Request::encodeFile($data['certificate']);
        }

        $result = Request::setWebhook($data);

        if (!$result->isOk()) {
            throw new TelegramException(
                'Webhook was not set! Error: ' . $result->getErrorCode() . ' ' . $result->getDescription()
            );
        }

        return $result;
    }

    /**
     * Delete any assigned webhook
     *
     * @param array $data
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function deleteWebhook(array $data = []): ServerResponse
    {
        $result = Request::deleteWebhook($data);

        if (!$result->isOk()) {
            throw new TelegramException(
                'Webhook was not deleted! Error: ' . $result->getErrorCode() . ' ' . $result->getDescription()
            );
        }

        return $result;
    }

    /**
     * Replace function `ucwords` for UTF-8 characters in the class definition and commands
     *
     * @param string $str
     * @param string $encoding (default = 'UTF-8')
     *
     * @return string
     */
    protected function ucWordsUnicode(string $str, string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($str, MB_CASE_TITLE, $encoding);
    }

    /**
     * Replace function `ucfirst` for UTF-8 characters in the class definition and commands
     *
     * @param string $str
     * @param string $encoding (default = 'UTF-8')
     *
     * @return string
     */
    protected function ucFirstUnicode(string $str, string $encoding = 'UTF-8'): string
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
            . mb_strtolower(mb_substr($str, 1, mb_strlen($str), $encoding), $encoding);
    }

    /**
     * Enable requests limiter
     *
     * @param array $options
     *
     * @return Telegram
     * @throws TelegramException
     */
    public function enableLimiter(array $options = []): Telegram
    {
        Request::setLimiter(true, $options);

        return $this;
    }
}
