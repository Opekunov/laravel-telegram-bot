<?php

namespace Opekunov\LaravelTelegramBot\Actions;

/**
 * Test action
 * @deprecated
 */
class TestAction implements Action
{

    public function __invoke()
    {
        return true;
    }
}
