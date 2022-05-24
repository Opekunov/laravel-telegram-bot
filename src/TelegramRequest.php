<?php

namespace Opekunov\LaravelTelegramBot;

use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;

/**
 * Parser for telegram webhook/update request
 * TODO: Tests
 *
 * @method bool isInlineQuery()
 * @method bool isCallbackQuery()
 * @method bool isEditedMessage()
 * @method bool isReply()
 * @method bool isMessage()
 * @method bool isPhoto()
 * @method bool isVideo()
 * @method bool isAudio()
 * @method bool isVoice()
 * @method bool isAnimation()
 * @method bool isSticker()
 * @method bool isDocument()
 * @method bool isLocation()
 * @method bool isContact()
 * @method bool isChannelPost()
 *
 * @author Opekunov
 * @author Refactored https://github.com/Eleirbag89/TelegramBotPHP/blob/master/Telegram.php
 */
class TelegramRequest
{
    /**  Constant for type Inline Query. */
    const INLINE_QUERY = 'inline_query';
    /**  Constant for type Callback Query. */
    const CALLBACK_QUERY = 'callback_query';
    /**  Constant for type Edited Message. */
    const EDITED_MESSAGE = 'edited_message';
    /**  Constant for type Reply. */
    const REPLY = 'reply';
    /**  Constant for type Message. */
    const MESSAGE = 'message';
    /**  Constant for type Photo. */
    const PHOTO = 'photo';
    /**  Constant for type Video. */
    const VIDEO = 'video';
    /**  Constant for type Audio. */
    const AUDIO = 'audio';
    /**  Constant for type Voice. */
    const VOICE = 'voice';
    /**  Constant for type animation. */
    const ANIMATION = 'animation';
    /**  Constant for type sticker. */
    const STICKER = 'sticker';
    /**  Constant for type Document. */
    const DOCUMENT = 'document';
    /**  Constant for type Location. */
    const LOCATION = 'location';
    /**  Constant for type Contact. */
    const CONTACT = 'contact';
    /**  Constant for type Channel Post. */
    const CHANNEL_POST = 'channel_post';

    protected array $data;
    protected int $updateId;

    /**
     * @param  array  $updateData
     *
     * @throws TelegramException
     */
    public function __construct(array $updateData)
    {
        if (!isset($updateData['update_id'])) {
            throw new TelegramException('Update data hasn\'t update_id');
        }

        $this->updateId = $updateData['update_id'];
        $this->data = $updateData;
    }

    public function __call($name, $arguments)
    {
        if (mb_strpos($name, 'is') !== false) {
            $name = str_replace('is', '', $name);
            $type = strtoupper(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
            if (!defined("static::$type")) {
                throw new TelegramException("$type not exists");
            }

            return $this->getUpdateType() === constant("static::$type");
        } else {
            throw new TelegramException("Method $name() not found");
        }
    }

    /**
     * Return current update type `False` on failure.
     *
     * @return false|string
     */
    public function getUpdateType()
    {
        $update = $this->data;
        if (isset($update['inline_query'])) {
            return self::INLINE_QUERY;
        }
        if (isset($update['callback_query'])) {
            return self::CALLBACK_QUERY;
        }
        if (isset($update['edited_message'])) {
            return self::EDITED_MESSAGE;
        }
        if (isset($update['message']['text'])) {
            return self::MESSAGE;
        }
        if (isset($update['message']['photo'])) {
            return self::PHOTO;
        }
        if (isset($update['message']['video'])) {
            return self::VIDEO;
        }
        if (isset($update['message']['audio'])) {
            return self::AUDIO;
        }
        if (isset($update['message']['voice'])) {
            return self::VOICE;
        }
        if (isset($update['message']['contact'])) {
            return self::CONTACT;
        }
        if (isset($update['message']['location'])) {
            return self::LOCATION;
        }
        if (isset($update['message']['reply_to_message'])) {
            return self::REPLY;
        }
        if (isset($update['message']['animation'])) {
            return self::ANIMATION;
        }
        if (isset($update['message']['sticker'])) {
            return self::STICKER;
        }
        if (isset($update['message']['document'])) {
            return self::DOCUMENT;
        }
        if (isset($update['channel_post'])) {
            return self::CHANNEL_POST;
        }

        return false;
    }

    /**
     * Return true if caption/text/callback_data has `$needle`
     *
     * @param  string  $needle
     *
     * @return bool
     */
    public function textHas(string $needle): bool
    {
        return mb_stripos($this->text(), $needle) !== false;
    }

    /**
     * Get the text of the current message
     *
     * @return string the String users's text.
     */
    public function text(): string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['data'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['text'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['text'];
        }

        return @$this->data['message']['text'];
    }

    public function caption()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['caption'];
        }

        return @$this->data['message']['caption'];
    }

    /**
     * Get the chat_id of the current message
     *
     * @return int|null users's chat_id.
     */
    public function chatID(): ?int
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['message']['chat']['id'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['chat']['id'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['chat']['id'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['from']['id'];
        }

        return $this->data['message']['chat']['id'];
    }

    /**
     * Get the message_id of the current message
     *
     * @return int|null message_id.
     */
    public function messageID(): ?int
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['message']['message_id'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['message_id'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['message_id'];
        }

        return $this->data['message']['message_id'];
    }

    /**
     * Get the reply_to_message message_id of the current message
     *
     * @return int|null reply_to_message message_id.
     */
    public function replyToMessageID(): ?int
    {
        return $this->data['message']['reply_to_message']['message_id'];
    }

    /**
     * Get the reply_to_message forward_from user_id of the current message
     *
     * @return int|null reply_to_message forward_from user_id.
     */
    public function replyToMessageFromUserID(): ?int
    {
        return $this->data['message']['reply_to_message']['forward_from']['id'];
    }

    /**
     * Get the inline_query of the current update
     *
     * @return array inline_query.
     */
    public function inlineQuery(): array
    {
        return $this->data['inline_query'];
    }

    /**
     * Get the callback_query of the current update
     *
     * @return string callback_query.
     */
    public function callbackQuery(): string
    {
        return $this->data['callback_query'];
    }

    /**
     * Get the callback_query id of the current update
     *
     * @return string callback_query id.
     */
    public function callbackID(): string
    {
        return $this->data['callback_query']['id'];
    }

    /**
     * Get the data of the current callback
     *
     * @return string callback_data.
     * @deprecated Use Text() instead
     */
    public function callbackData(): string
    {
        return $this->data['callback_query']['data'];
    }

    /**
     * Get the message of the current callback
     *
     * @return array the Message.
     */
    public function callbackMessage(): array
    {
        return $this->data['callback_query']['message'];
    }

    /**
     * Get the chat_id of the current callback
     *
     * @return int chat id.
     * @deprecated Use ChatId() instead
     */
    public function callbackChatID(): int
    {
        return $this->data['callback_query']['message']['chat']['id'];
    }

    /**
     * Get the from_id of the current callback
     *
     * @return int callback_query from_id.
     */
    public function callbackFromID(): int
    {
        return $this->data['callback_query']['from']['id'];
    }

    /**
     * Get the date of the current message
     *
     * @return int message's date.
     */
    public function date(): int
    {
        return $this->data['message']['date'];
    }

    /**
     * User first name + last name
     *
     * @return string
     */
    public function userFullName(): string
    {
        return trim($this->firstName().' '.$this->lastName());
    }

    /**
     * Get the first name of the user
     *
     * @return string|null
     */
    public function firstName(): ?string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['from']['first_name'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['from']['first_name'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['first_name'];
        }

        return @$this->data['message']['from']['first_name'];
    }

    /**
     * Get the last name of the user
     *
     * @return string|null
     */
    public function lastName(): ?string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['from']['last_name'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['from']['last_name'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['last_name'];
        }
        if ($type == self::MESSAGE) {
            return @$this->data['message']['from']['last_name'];
        }

        return '';
    }

    /**
     * Get the username of the user
     *
     * @return string|null
     */
    public function username(): ?string
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['from']['username'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['from']['username'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['username'];
        }

        return @$this->data['message']['from']['username'];
    }

    /**
     * Get the location in the message
     *
     * @return array
     */
    public function location(): array
    {
        return $this->data['message']['location'];
    }

    /**
     * Get the update_id of the message
     *
     * @return int
     */
    public function updateID(): int
    {
        return $this->updateId;
    }

    /**
     * Get user's id of current message
     *
     * @return int|null
     */
    public function userID(): ?int
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return $this->data['callback_query']['from']['id'];
        }
        if ($type == self::CHANNEL_POST) {
            return $this->data['channel_post']['from']['id'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['id'];
        }

        return $this->data['message']['from']['id'];
    }

    /**
     * Get user's id of current forwarded message
     *
     * @return int|null
     */
    public function fromID(): ?int
    {
        return $this->data['message']['forward_from']['id'];
    }

    /**
     * Get chat's id where current message forwarded from
     *
     * @return int
     */
    public function fromChatID(): int
    {
        return $this->data['message']['forward_from_chat']['id'];
    }

    /**
     * Tell if a message is from a group or user chat
     *
     * @return bool true if the message is from a Group chat, false otherwise.
     */
    public function messageFromGroup(): bool
    {
        if ($this->data['message']['chat']['type'] == 'private') {
            return false;
        }

        return true;
    }

    /**
     * Tell if a message is from a private chat
     *
     * @return bool true if the message is from a Private chat, false otherwise.
     */
    public function messageFromPrivate(): bool
    {
        if ($this->data['message']['chat']['type'] == 'private') {
            return true;
        }

        return false;
    }

    /**
     * Get the contact phone number
     *
     * @return string of the contact phone number.
     */
    public function getContactPhoneNumber(): string
    {
        if ($this->getUpdateType() == self::CONTACT) {
            return $this->data['message']['contact']['phone_number'];
        }

        return '';
    }

    /**
     * Get the title of the group chat
     *
     * @return string of the title chat.
     */
    public function messageFromGroupTitle(): string
    {
        if ($this->data['message']['chat']['type'] != 'private') {
            return $this->data['message']['chat']['title'];
        }

        return '';
    }
}