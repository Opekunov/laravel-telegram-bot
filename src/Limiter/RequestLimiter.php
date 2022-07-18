<?php

namespace Opekunov\LaravelTelegramBot\Limiter;

use Illuminate\Support\Facades\Cache;

class RequestLimiter implements Limiter
{
    protected const LIMITER_CACHE_KEY = 'tg_limiter';
    protected const PARTICULAR_LIMITER_CACHE_KEY = 'tg_limiter_for_';

    protected int $requestsForParticular = 1;
    protected int $limitForParticular = 1;
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

    /**
     * @return int
     */
    public function getRequestsForParticular(): int
    {
        return $this->requestsForParticular;
    }

    /**
     * @return int
     */
    public function getLimitForParticular(): int
    {
        return $this->limitForParticular;
    }

    /**
     * @return int
     */
    public function getRequestsForDifference(): int
    {
        return $this->requestsForDifference;
    }

    /**
     * @return int
     */
    public function getLimitForDifference(): int
    {
        return $this->limitForDifference;
    }

    /**
     * @return int
     */
    public function getRequestsForGroups(): int
    {
        return $this->requestsForGroups;
    }

    /**
     * @return int
     */
    public function getLimitForGroups(): int
    {
        return $this->limitForGroups;
    }

    public function increase(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        if (!$this->isLimitedMethod($method, $chatId, $inlineMessageId)) {
            return true;
        }

        $isGroup = $this->isGroup($chatId, $inlineMessageId);

        $limiters = $this->getLimiters();
        $limiters->groups->incrementIf(fn() => $isGroup);
        $limiters->difference->increment();
        $particular = $this->getParticularLimiter($chatId);
        $particular->increment();

        return $this->saveLimiters($limiters, $particular, $chatId);
    }

    protected function isLimitedMethod(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        return ($chatId || $inlineMessageId) && in_array($method, $this->limitedMethods, true);
    }

    protected function isGroup(?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        return (is_numeric($chatId) && $chatId < 0) || $inlineMessageId;
    }

    public function getLimiters(): LimitersGroup
    {
        return Cache::get(self::LIMITER_CACHE_KEY, $this->createLimiters());
    }

    /**
     * @return LimitersGroup
     */
    protected function createLimiters(): LimitersGroup
    {
        return new LimitersGroup(
            new LimiterObject($this->limitForGroups),
            new LimiterObject($this->limitForDifference),
        );
    }

    public function getParticularLimiter(?int $key = null): LimiterObject
    {
        if (!$key) {
            return new LimiterObject($this->limitForParticular);
        }
        return Cache::get(self::PARTICULAR_LIMITER_CACHE_KEY.$key, new LimiterObject($this->limitForParticular));
    }

    public function saveLimiters(LimitersGroup $limiters, LimiterObject $particular, ?int $particularKey = null): bool
    {
        $maxTime = max($this->limitForGroups, $this->limitForDifference);
        $groupPut = Cache::put(self::LIMITER_CACHE_KEY, $limiters, $maxTime);

        return !$particularKey ? $groupPut
            : $groupPut && Cache::put(self::PARTICULAR_LIMITER_CACHE_KEY.$particularKey, $particular, $this->limitForParticular);
    }

    public function checkLimit(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool
    {
        if (!$this->isLimitedMethod($method, $chatId, $inlineMessageId)) {
            return true;
        }

        $limiters = $this->getLimiters();
        $particular = $this->getParticularLimiter($chatId);
        $limiters->difference->touch();
        $limiters->groups->touch();
        $particular->touch();
        
        return $particular->counter <= $this->requestsForParticular
            && ($limiters->groups->counter <= $this->requestsForGroups || !$this->isGroup($chatId, $inlineMessageId))
            && $limiters->difference->counter <= $this->requestsForDifference;
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
}
