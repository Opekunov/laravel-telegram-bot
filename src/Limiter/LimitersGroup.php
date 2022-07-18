<?php

namespace Opekunov\LaravelTelegramBot\Limiter;

class LimitersGroup
{
    public function __construct(public LimiterObject $groups, public LimiterObject $difference)
    {
    }
}
