<?php

namespace Opekunov\LaravelTelegramBot\Limiter;

use Carbon\Carbon;

class LimiterObject
{
    public Carbon $createdAt;
    public Carbon $updatedAt;
    public Carbon $destroyAt;
    public int $counter = 0;

    /**
     * @param  int  $ttl  Time in seconds for destroy
     */
    public function __construct(public int $ttl)
    {
        $this->updatedAt = $this->createdAt = now();
        $this->destroyAt = $this->updatedAt->clone()->addSeconds($this->ttl);
    }

    public function touch(): void
    {
        if($this->getTimeLeft() < 0) {
            $this->reset();
        }
    }

    public function increment(int $step = 1): void
    {
        $this->counter += $step;
        $this->updatedAt = now();
        $this->touch();
    }

    public function incrementIf(callable $statement, int $step = 1): void
    {
        if($statement()){
            $this->increment($step);
        }
    }

    public function getTimeLeft(): int
    {
        return now()->diffInSeconds($this->destroyAt, false);
    }

    public function reset(): void
    {
        $this->counter = 1;
        $this->updatedAt = $this->createdAt = now();
        $this->destroyAt = $this->createdAt->clone()->addSeconds($this->ttl);
    }
}
