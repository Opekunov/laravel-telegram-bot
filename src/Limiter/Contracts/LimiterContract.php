<?php

namespace Opekunov\LaravelTelegramBot\Limiter\Contracts;

interface LimiterContract
{
    /**
     * Create new Limiter instance
     *
     * @param  string  $key  unique instance key
     * @param  int  $max  maximum counter value. Used for comparison only
     * @param  int  $ttl  time before state reset
     */
    public function __construct(string $key, int $max, int $ttl);

    /**
     * Return exists Limiter instance or create new
     *
     * @param  string  $key  unique instance key
     * @param  int  $max  maximum counter value. Used for comparison only
     * @param  int  $ttl  time before state reset
     *
     * @return self
     */
    public static function getOrCreate(string $key, int $max, int $ttl): self;

    /**
     * Update state
     *
     * @return void
     */
    public function touch(): void;

    /**
     * Returns the time left to reset in milliseconds
     *
     * @return int
     */
    public function getTimeLeft(): int;

    /**
     * Reset state
     *
     * @return void
     */
    public function reset(): void;

    /**
     * Get time when created in unix milliseconds
     *
     * @return int
     */
    public function getCreatedAt(): int;

    /**
     * Set the time when created
     *
     * @param  int  $createdAt  Tie in unix milliseconds
     *
     * @return void
     */
    public function setCreatedAt(int $createdAt): void;

    /**
     * Get time when last updated in unix milliseconds
     *
     * @return int
     */
    public function getUpdatedAt(): int;

    /**
     * Set the time when last updated
     *
     * @param  int  $updatedAt  Tie in unix milliseconds
     *
     * @return void
     */
    public function setUpdatedAt(int $updatedAt): void;

    /**
     * Get time when need reset state in unix milliseconds
     *
     * @return int
     */
    public function getDestroyAt(): int;

    /**
     * Set time when need reset state in unix milliseconds
     *
     * @param  int  $destroyAt
     *
     * @return void
     */
    public function setDestroyAt(int $destroyAt): void;

    /**
     * Get current counter
     *
     * @return int
     */
    public function getCounter(): int;

    /**
     * Set current counter
     *
     * @param  int  $counter
     *
     * @return void
     */
    public function setCounter(int $counter): void;

    /**
     * Get maximum counter value. Used for comparison only
     *
     * @return int
     */
    public function getMax(): int;

    /**
     * Set maximum counter value. Used for comparison only
     *
     * @param  int  $max
     *
     * @return void
     */
    public function setMax(int $max): void;

    /**
     * Get time before state reset
     *
     * @return int
     */
    public function getTtl(): int;

    /**
     * Set time before state reset
     *
     * @param  int  $ttl
     *
     * @return void
     */
    public function setTtl(int $ttl): void;

    /**
     * Get unique instance key
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Set unique instance key
     *
     * @param  string  $key
     *
     * @return void
     */
    public function setKey(string $key): void;

    /**
     * Increment instance counter if condition is true
     *
     * @param  callable  $statement
     * @param  int  $step
     *
     * @return void
     */
    public function incrementIf(callable $statement, int $step = 1): void;

    /**
     * Increment instance counter
     *
     * @param  int  $step
     *
     * @return void
     */
    public function increment(int $step = 1): void;

    /**
     * Save instance to storage
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Checks the condition ">=" for $max or for $compared (if specified)
     *
     * @param  int|null  $compared
     *
     * @return bool
     */
    public function isMoreOrEqual(?int $compared = null): bool;

    /**
     * Checks the condition ">" for $max or for $compared (if specified)
     *
     * @param  int|null  $compared
     *
     * @return bool
     */
    public function isMore(?int $compared = null): bool;

    /**
     * Checks the condition "<=" for $max or for $compared (if specified)
     *
     * @param  int|null  $compared
     *
     * @return bool
     */
    public function isLessOrEqual(?int $compared = null): bool;

    /**
     * Checks the condition "<" for $max or for $compared (if specified)
     *
     * @param  int|null  $compared
     *
     * @return bool
     */
    public function isLess(?int $compared = null): bool;
}
