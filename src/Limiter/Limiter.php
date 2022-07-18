<?php
/*
 * Copyright (c)
 * Opekunov Aleksandr <iam@opekunov.com>
 */

namespace Opekunov\LaravelTelegramBot\Limiter;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;

/**
 * TODO: Optimize cache requests
 */
class Limiter implements Arrayable, Contracts\LimiterContract
{
    private int $createdAt;
    private int $updatedAt;
    private int $destroyAt;
    private int $counter = 0;
    private int $max = 0;
    private int $ttl = 0;
    private string $key = '';

    /**
     * Create new cache Limiter instance
     *
     * @param  string  $key  unique instance key
     * @param  int  $max  maximum counter value. Used for comparison only
     * @param  int  $ttl  time before state reset in seconds
     */
    public function __construct(string $key, int $max, int $ttl)
    {
        $this->updatedAt = $this->createdAt = $this->now();
        $this->destroyAt = $this->createdAt + ($ttl * 1000);
        $this->key = $key;
        $this->max = $max;
        $this->ttl = $ttl;
    }

    public static function getOrCreate(string $key, int $max, int $ttl): self
    {
        /** @var self $limiter */
        $limiter = Cache::get($key, new self($key, $max, $ttl));
        $limiter->touch();
        return $limiter;
    }

    public function touch(): void
    {
        $this->save();
        if ($this->getTimeLeft() <= 0) {
            $this->reset();
        }
    }

    public function getTimeLeft(): int
    {
        $now = $this->now();
        return $this->destroyAt - $now;
    }

    protected function now(): int
    {
        return intval(floor(microtime(true) * 1000));
    }

    public function reset(): void
    {
        $this->counter = 0;
        $this->updatedAt = $this->createdAt = $this->now();
        $this->destroyAt = $this->createdAt + ($this->ttl * 1000);
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDestroyAt(): int
    {
        return $this->destroyAt;
    }

    public function setDestroyAt(int $destroyAt): void
    {
        $this->destroyAt = $destroyAt;
    }

    public function getCounter(): int
    {
        $this->touch();
        return $this->counter;
    }

    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function incrementIf(callable $statement, int $step = 1): void
    {
        if ($statement()) {
            $this->increment($step);
        }
    }

    public function increment(int $step = 1): void
    {
        $this->counter += $step;
        $this->updatedAt = $this->now();
        $this->touch();
    }

    public function save(): bool
    {
        return Cache::put($this->key, $this, intval($this->getTimeLeft() / 1000));
    }

    public function isMoreOrEqual(?int $compared = null): bool
    {
        $this->touch();
        return $this->counter >= (is_null($compared) ? $this->max : $compared);
    }

    public function isMore(?int $compared = null): bool
    {
        $this->touch();
        return $this->counter > (is_null($compared) ? $this->max : $compared);
    }

    public function isLessOrEqual(?int $compared = null): bool
    {
        $this->touch();
        return $this->counter <= (is_null($compared) ? $this->max : $compared);
    }

    public function isLess(?int $compared = null): bool
    {
        $this->touch();
        return $this->counter < (is_null($compared) ? $this->max : $compared);
    }

    public function toArray(): array
    {
        $this->touch();
        return get_object_vars($this);
    }
}
