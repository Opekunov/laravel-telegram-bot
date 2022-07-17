<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

namespace Opekunov\LaravelTelegramBot;

use Opekunov\LaravelTelegramBot\Entities\Chat;
use Opekunov\LaravelTelegramBot\Entities\Update;
use Opekunov\LaravelTelegramBot\Entities\User;

class Handler
{
    private Update $update;

    public function __construct(array|string $data)
    {
        $this->update = is_string($data) ? new Update(json_decode($data, true)) : new Update($data);
    }

    public function getChat(): ?Chat
    {
        $u = $this->update;
        return $u->getMessage()?->getChat()
            ?? $u->getChatJoinRequest()?->getChat()
            ?? $u->getChatMember()?->getChat()
            ?? $u->getMyChatMember()?->getChat()
            ?? $u->getCallbackQuery()?->getMessage()->getChat()
            ?? $u->getChannelPost()?->getChat()
            ?? $u->getEditedChannelPost()?->getChat()
            ?? $u->getEditedMessage()?->getChat();
    }

    public function getSenderChat(): ?Chat
    {
        $u = $this->update;
        return $u->getMessage()?->getSenderChat()
            ?? $u->getCallbackQuery()?->getMessage()->getSenderChat()
            ?? $u->getChannelPost()?->getSenderChat()
            ?? $u->getEditedChannelPost()?->getSenderChat()
            ?? $u->getEditedMessage()?->getSenderChat();
    }

    public function getFrom(): ?User
    {
        $u = $this->update;
        return $u->getMessage()?->getFrom()
            ?? $u->getCallbackQuery()?->getFrom()
            ?? $u->getChannelPost()?->getFrom()
            ?? $u->getEditedChannelPost()?->getFrom()
            ?? $u->getEditedMessage()?->getFrom()
            ?? $u->getChatMember()?->getFrom()
            ?? $u->getMyChatMember()?->getFrom()
            ?? $u->getChatJoinRequest()?->getFrom()
            ?? $u->getChatMember()?->getFrom()
            ?? $u->getShippingQuery()->getFrom()
            ?? $u->getPollAnswer()->getUser()
            ?? $u->getPreCheckoutQuery()->getFrom()
            ?? $u->getChosenInlineResult()->getFrom()
            ?? $u->getInlineQuery()->getFrom();
    }

    public function getFromId(): ?int
    {
        return $this->getFrom()->getId();
    }

    public function getChatId(): ?int
    {
        return $this->getChat()->getId();
    }

    public function getSenderChatId(): ?int
    {
        return $this->getSenderChat()->getId();
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }

    public function getUpdateId(): int
    {
        return $this->update->getUpdateId();
    }
}
