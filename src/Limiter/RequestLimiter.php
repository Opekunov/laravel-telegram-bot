<?php
/*
 * Copyright (c)
 * Opekunov Aleksandr <iam@opekunov.com>
 */

namespace Opekunov\LaravelTelegramBot\Limiter;

use Opekunov\LaravelTelegramBot\Limiter\Contracts\LimiterContract;

/**
 * @method int getRequestsForParticular()
 * @method int getLimitForParticular()
 * @method int getRequestsForDifference()
 * @method int getLimitForDifference()
 * @method int getRequestsForGroups()
 * @method int getLimitForGroups()
 */
class RequestLimiter
{
    protected const GROUPS_LIMITER_KEY = 'otg_groups_limiter';
    protected const DIFFERENCE_LIMITER_KEY = 'otg_difference_limiter';
    protected const PARTICULAR_CACHE_KEY = 'otg_limiter_for_';

    protected int $requestsForParticular = 1;
    protected int $limitForParticular = 0;
    protected int $requestsForDifference = 30;
    protected int $limitForDifference = 1;
    protected int $requestsForGroups = 20;
    protected int $limitForGroups = 60;
    protected array $limitedMethods = [
        'sendMessage',
        'forwardMessage',
        'copyMessage',
        'sendPhoto',
        'sendAudio',
        'sendDocument',
        'sendSticker',
        'sendVideo',
        'sendAnimation',
        'sendVoice',
        'sendVideoNote',
        'sendMediaGroup',
        'sendLocation',
        'editMessageLiveLocation',
        'stopMessageLiveLocation',
        'sendVenue',
        'sendContact',
        'sendPoll',
        'sendDice',
        'sendInvoice',
        'sendGame',
        'setGameScore',
        'setMyCommands',
        'deleteMyCommands',
        'editMessageText',
        'editMessageCaption',
        'editMessageMedia',
        'editMessageReplyMarkup',
        'stopPoll',
        'setChatTitle',
        'setChatDescription',
        'setChatStickerSet',
        'deleteChatStickerSet',
        'setPassportDataErrors',
    ];

    protected string $limiterClass;
    protected string $queue = '';

    public function __construct(?string $limiter = null)
    {
        $this->limiterClass = $limiter ?? Limiter::class;
    }

    public function __call(string $method, array $arguments)
    {
        if (mb_strpos($method, 'get') === 0) {
            $method = str_replace('get', '', $method);
            $method[0] = strtolower($method[0]);
            return in_array($method, array_keys(get_object_vars($this))) ? $this->$method : throw new \Exception("Method $method not allowed");
        }

        return throw new \Exception("Method $method not allowed");
    }

    public function checkAndIncrease(string $method, ?int $chatId = null, ?int $inlineMessageId = null): int
    {
        if (!$ttl = $this->checkLimit($method, $chatId, $inlineMessageId)) {
            $this->increase($method, $chatId, $inlineMessageId);
        }
        return $ttl;
    }

    public function checkLimit(string $method, ?int $chatId = null, ?int $inlineMessageId = null): int
    {
        if (!$this->isLimitedMethod($method, $chatId, $inlineMessageId)) {
            return 0;
        }

        $isGroup = $this->isGroup($chatId, $inlineMessageId);

        $group = $this->getLimiter(self::GROUPS_LIMITER_KEY);
        $difference = $this->getLimiter(self::DIFFERENCE_LIMITER_KEY);
        $particular = $chatId ? $this->getLimiter(self::PARTICULAR_CACHE_KEY, (string)$chatId) : null;

        // Get all time lefts. Returns max
        $ttl = [
            $isGroup ? ($group->isLess() ? 0 : $group->getTimeLeft()) : 0,
            $chatId ? ($particular->isLess() ? 0 : $particular->getTimeLeft()) : 0,
            $difference->isLess() ? 0 : $difference->getTimeLeft()
        ];

        return max($ttl);
    }

    protected function isLimitedMethod(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        return ($chatId || $inlineMessageId) && in_array($method, $this->limitedMethods, true);
    }

    protected function isGroup(?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        return (is_numeric($chatId) && $chatId < 0) || $inlineMessageId;
    }

    protected function getLimiter(string $key, string $suffix = null): LimiterContract
    {
        if($key === self::PARTICULAR_CACHE_KEY){
            $cacheKey = "{$this->queue}_{$key}_$suffix";
        } else {
            $cacheKey = "{$this->queue}_{$key}";
        }

        return match ($key) {
            self::PARTICULAR_CACHE_KEY => $this->limiterClass::getOrCreate($cacheKey, $this->requestsForParticular, $this->limitForParticular),
            self::DIFFERENCE_LIMITER_KEY => $this->limiterClass::getOrCreate($cacheKey, $this->requestsForDifference, $this->limitForDifference),
            self::GROUPS_LIMITER_KEY => $this->limiterClass::getOrCreate($cacheKey, $this->requestsForGroups, $this->limitForGroups),
        };
    }

    public function increase(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        if (!$this->isLimitedMethod($method, $chatId, $inlineMessageId)) {
            return true;
        }

        $isGroup = $this->isGroup($chatId, $inlineMessageId);

        if ($isGroup) {
            $this->getLimiter(self::GROUPS_LIMITER_KEY)->increment();
        }
        $this->getLimiter(self::DIFFERENCE_LIMITER_KEY)->increment();
        if ($chatId) {
            $particular = $this->getLimiter(self::PARTICULAR_CACHE_KEY, (string)$chatId);
            $particular->increment();
        }

        return true;
    }

    public function increaseAndCheck(string $method, ?int $chatId = null, ?int $inlineMessageId = null): int
    {
        $this->increase($method, $chatId, $inlineMessageId);
        return $this->checkLimit($method, $chatId, $inlineMessageId);
    }

    public function setParticularLimits(int $requestsCount, int $perTime): void
    {
        $this->limitForParticular = $perTime;
        $this->requestsForParticular = $requestsCount;
    }

    public function setDifferenceLimits(int $requestsCount, int $perTime): void
    {
        $this->limitForDifference = $perTime;
        $this->requestsForDifference = $requestsCount;
    }

    public function setGroupsAndChannelsLimits(int $requestsCount, int $perTime): void
    {
        $this->limitForGroups = $perTime;
        $this->requestsForGroups = $requestsCount;
    }

    public function resetLimiters(?int $chatId = null)
    {
        $this->getLimiter(self::GROUPS_LIMITER_KEY)->reset();
        $this->getLimiter(self::DIFFERENCE_LIMITER_KEY)->reset();
        if ($chatId) {
            $this->getLimiter(self::PARTICULAR_CACHE_KEY, (string)$chatId)->reset();
        }
    }

    public function setQueue(string|int $queue): void
    {
        $this->queue = (string) $queue;
    }
}
