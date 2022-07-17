<?php

namespace Opekunov\LaravelTelegramBot\Entities\ChatMember;

use Opekunov\LaravelTelegramBot\Entities\Entity;

class Factory extends \Opekunov\LaravelTelegramBot\Entities\Factory
{
    public static function make(array $data, string $bot_username): Entity
    {
        $type = [
            'creator'       => ChatMemberOwner::class,
            'administrator' => ChatMemberAdministrator::class,
            'member'        => ChatMemberMember::class,
            'restricted'    => ChatMemberRestricted::class,
            'left'          => ChatMemberLeft::class,
            'kicked'        => ChatMemberBanned::class,
        ];

        if (!isset($type[$data['status'] ?? ''])) {
            return new ChatMemberNotImplemented($data, $bot_username);
        }

        $class = $type[$data['status']];
        return new $class($data, $bot_username);
    }
}
