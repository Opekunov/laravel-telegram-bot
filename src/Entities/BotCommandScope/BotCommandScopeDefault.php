<?php

namespace Opekunov\LaravelTelegramBot\Entities\BotCommandScope;

use Opekunov\LaravelTelegramBot\Entities\Entity;

/**
 * Class BotCommandScopeDefault
 *
 * @link https://core.telegram.org/bots/api#botcommandscopedefault
 */
class BotCommandScopeDefault extends Entity implements BotCommandScope
{
    public function __construct(array $data = [])
    {
        $data['type'] = 'default';
        parent::__construct($data);
    }
}
