<?php

namespace Opekunov\LaravelTelegramBot\Entities\ChatMember;

use Opekunov\LaravelTelegramBot\Entities\Entity;
use Opekunov\LaravelTelegramBot\Entities\User;

/**
 * Class ChatMemberNotImplemented
 *
 * @method string getStatus() The member's status in the chat
 * @method User   getUser()   Information about the user
 */
class ChatMemberNotImplemented extends Entity implements ChatMember
{
    //
}
