<?php

namespace Opekunov\LaravelTelegramBot\Limiter;

interface Limiter
{
    /**
     * Get limiters from Storage
     *
     * @return LimitersGroup
     */
    public function getLimiters(): LimitersGroup;

    /**
     * Get particular limiter by key from Storage
     *
     * @param  int|null  $key
     *
     * @return LimiterObject
     */
    public function getParticularLimiter(?int $key = null): LimiterObject;

    /**
     * Storage current limiters state.
     * By default - cache
     *
     * @param  LimitersGroup  $limiters  Group and Difference LimiterObject
     * @param  LimiterObject  $particular  Particular LimiterObject
     * @param  int|null  $particularKey  Particular unique key
     *
     * @return bool
     */
    public function saveLimiters(LimitersGroup $limiters, LimiterObject $particular, ?int $particularKey = null): bool;

    /**
     * Increase limiters
     *
     * @param  string  $method Request method
     * @param  int|null  $chatId Send to Chat ID
     * @param  int|null  $inlineMessageId Or InlineMessageId
     *
     * @return bool
     */
    public function increase(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool;

    /**
     * Check limits for current Request
     *
     * @param  string  $method  Request method
     * @param  int|null  $chatId  Send to Chat ID
     * @param  int|null  $inlineMessageId  Or InlineMessageId
     *
     * @return bool|int If time limit detected return Int time in seconds for next request, else return true
     */
    public function checkLimit(string $method, ?int $chatId = null, ?int $inlineMessageId = null): bool|int;

    /**
     * Set groups and channels limits
     * By default - no more than 20 messages per minute in groups and channels
     *
     * @param  int  $requestsCount
     * @param  int  $perTime
     *
     * @see https://core.telegram.org/bots/faq#broadcasting-to-users
     */
    public function setGroupsAndChannelsLimits(int $requestsCount, int $perTime): void;

    /**
     * Set difference chats limits.
     * By default - no more than 30 messages per second to different chats
     *
     * @param  int  $requestsCount
     * @param  int  $perTime
     *
     * @see https://core.telegram.org/bots/faq#broadcasting-to-users
     */
    public function setDifferenceLimits(int $requestsCount, int $perTime): void;

    /**
     * Set particular chat limits.
     * By default - no limits
     *
     * @param  int  $requestsCount
     * @param  int  $perTime
     *
     * @see https://core.telegram.org/bots/faq#broadcasting-to-users
     */
    public function setParticularLimits(int $requestsCount, int $perTime): void;
}
