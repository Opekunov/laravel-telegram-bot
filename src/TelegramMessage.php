<?php

namespace Opekunov\LaravelTelegramBot;

use GuzzleHttp\Exception\GuzzleException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;

class TelegramMessage extends Telegram
{
    const MAX_MESSAGE_LENGTH = 4096;
    const MAX_CAPTION_LENGTH = 1024;

    protected array $payload = [
        'parse_mode' => 'MarkdownV2'
    ];

    protected bool $isTextMessage = true;
    protected bool $isPhoto = false;
    protected bool $isVideo = false;
    protected bool $isAudio = false;
    private bool $isAnimation = false;
    private bool $isDocument = false;

    /**
     * Create telegram message instance
     *
     * @param  string|null  $botToken
     * @param  string|null  $baseApiUri  default is https://api.telegram.org
     * @param  string  $botUsername
     *
     * @return TelegramMessage
     * @throws TelegramException
     */
    public static function init(string $botToken = null, ?string $baseApiUri = null, string $botUsername = ''): TelegramMessage
    {
        return new self($botToken, $baseApiUri, $botUsername);
    }

    /**
     * Generate prepared inline button
     *
     * @param  string  $text
     * @param  string|null  $url
     * @param  string|null  $callbackData  1-64 bytes
     *
     * @return string[]
     * @throws TelegramException
     */
    public static function inlineButton(string $text, string $url = null, string $callbackData = null): array
    {
        $button = ['text' => $text];

        if (is_string($callbackData) && (empty($callbackData) || strlen($callbackData) > 64)) {
            throw new TelegramException('Callback data empty or more than 64 bytes');
        } elseif ($callbackData) {
            $button['callback_data'] = $callbackData;
        }

        if ($url) {
            $button['url'] = $url;
        }

        return $button;
    }

    /**
     * Use this method to forward messages of any kind.
     * Service messages can't be forwarded. On success, the sent Message is returned.
     *
     * @param  int  $chatId  Unique identifier for the target chat or username of the target channel (in the format @channelusername)
     * @param  int  $fromChatId  Unique identifier for the chat where the original message was sent (or channel username in the format
     * @param  int  $messageId  Message identifier in the chat specified in from_chat_id
     * @param  bool  $disableNotification  Sends the message silently. Users will receive a notification with no sound.
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws Exceptions\TelegramRequestException
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws GuzzleException
     * @channelusername)
     * @see https://core.telegram.org/bots/api#forwardmessage
     */
    public function forward(int $chatId, int $fromChatId, int $messageId, bool $disableNotification = false): array
    {
        return $this->sendRequest('forwardMessage', [
            'chat_id'              => $chatId,
            'from_chat_id'         => $fromChatId,
            'message_id'           => $messageId,
            'disable_notification' => $disableNotification
        ]);
    }

    /**
     * Use this method to copy messages of any kind. Service messages and invoice messages can't be copied.
     * The method is analogous to the method forwardMessage, but the copied message doesn't have a link to the original message.
     * Returns the MessageId of the sent message on success.
     *
     * @param  int  $chatId  Unique identifier for the target chat or username of the target channel (in the format @channelusername)
     * @param  int  $fromChatId  Unique identifier for the chat where the original message was sent
     * (or channel username in the format  @channelusername)
     * @param  int  $messageId  Message identifier in the chat specified in from_chat_id
     * @param  bool  $disableNotification  Sends the message silently. Users will receive a notification with no sound.
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws Exceptions\TelegramRequestException
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws GuzzleException
     * @see https://core.telegram.org/bots/api#copymessage
     */
    public function copy(int $chatId, int $fromChatId, int $messageId, bool $disableNotification = false)
    {
        return $this->sendRequest('copyMessage', [
            'chat_id'              => $chatId,
            'from_chat_id'         => $fromChatId,
            'message_id'           => $messageId,
            'disable_notification' => $disableNotification
        ]);
    }

    /**
     * Set message text content
     *
     * @param  string  $content  String. 1-4096 characters
     *
     * @return $this
     * @throws TelegramException
     */
    public function content(string $content): TelegramMessage
    {
        if (empty($content)) {
            throw new TelegramException('Message content not defined');
        }
        $contentLength = mb_strlen($content);
        if ($contentLength > self::MAX_MESSAGE_LENGTH) {
            throw new TelegramException('Message text too large. Max: '.self::MAX_MESSAGE_LENGTH.'. Passed: '.$contentLength);
        }
        $this->payload['text'] = $content;
        return $this;
    }

    /**
     * Set Chat ID for sending
     *
     * @param  int  $chatId
     *
     * @return $this
     */
    public function sendTo(int $chatId): TelegramMessage
    {
        $this->payload['chat_id'] = $chatId;
        return $this;
    }

    /**
     * Send message
     *
     * @param  int|null  $chatId
     *
     * @return array
     * @throws TelegramException
     */
    public function send(int $chatId = null): array
    {
        if (empty($this->payload['chat_id']) && empty($chatId)) {
            throw new TelegramException('Chat ID not defined');
        } elseif ($chatId) {
            $this->payload['chat_id'] = $chatId;
        }
        $this->escapeByParseMode();

        if (isset($this->payload['reply_markup'])) {
            $this->payload['reply_markup'] = json_encode($this->payload['reply_markup']);
        }

        if ($this->isPhoto) {
            return $this->sendPhoto();
        } elseif ($this->isVideo) {
            return $this->sendVideo();
        } elseif ($this->isAnimation) {
            return $this->sendAnimation();
        } elseif ($this->isDocument) {
            return $this->sendDocument();
        } else {
            return $this->sendMessage();
        }
    }

    /**
     * Escape text data by parse mode
     *
     * @return void
     */
    private function escapeByParseMode()
    {
        if ($this->payload['parse_mode'] === 'MarkdownV2') {
            if (isset($this->payload['text'])) {
                $this->payload['text'] = $this->escapeMarkdownV2($this->payload['text']);
            } elseif (isset($this->payload['caption'])) {
                $this->payload['caption'] = $this->escapeMarkdownV2($this->payload['caption']);
            }
        }
    }

    /**
     * Escape markdown (v2) special characters
     *
     * @see https://core.telegram.org/bots/api#markdownv2-style
     *
     * @param  string  $string
     *
     * @author https://github.com/noplanman
     *
     * @return string
     */
    private function escapeMarkdownV2(string $string): string
    {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'],
            $string
        );
    }

    /**
     * Send Photo
     *
     * @see https://core.telegram.org/bots/api#sendphoto
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    protected function sendPhoto(): array
    {
        return $this->sendRequest('sendPhoto', $this->payload);
    }

    /**
     * Send Video
     *
     * @see https://core.telegram.org/bots/api#sendvideo
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    protected function sendVideo(): array
    {
        return $this->sendRequest('sendVideo', $this->payload);
    }

    /**
     * Send Animation
     *
     * @see https://core.telegram.org/bots/api#sendanimation
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    protected function sendAnimation(): array
    {
        return $this->sendRequest('sendAnimation', $this->payload);
    }

    /**
     * Send Document
     *
     * @see https://core.telegram.org/bots/api#senddocument
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    protected function sendDocument(): array
    {
        return $this->sendRequest('sendDocument', $this->payload);
    }

    /**
     * Send Message
     *
     * @see https://core.telegram.org/bots/api#sendmessage
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    protected function sendMessage(): array
    {
        return $this->sendRequest('sendMessage', $this->payload);
    }

    /**
     * Send answer callback query
     *
     * https://core.telegram.org/bots/api#answercallbackquery
     *
     * @param  string  $callbackQueryId  Unique identifier for the query to be answered
     * @param  string|null  $text  Text of the notification. If not specified, nothing will be shown to the user, 0-200 characters
     * @param  bool  $showAlert  If True, an alert will be shown by the client instead of a notification at the top of the chat screen. Defaults to
     *     false.
     * @param  string|null  $url  URL that will be opened by the user's client. If you have created a Game and accepted the conditions via BotFather,
     * specify the URL that opens your game - note that this will only work if the query comes from a callback_game button. Otherwise, you may use
     *     links like t.me/your_bot?start=XXXX that open your bot with a parameter.
     * @param  int  $cacheTime  The maximum amount of time in seconds that the result of the callback query may be cached client-side. Telegram apps
     *     will support caching starting in version 3.14. Defaults to 0.
     *
     * @return array
     * @throws Exceptions\TelegramRequestException
     * @throws Exceptions\TelegramTooManyRequestsException|TelegramException
     */
    public function sendAnswerCallbackQuery(
        string $callbackQueryId,
        string $text = '',
        bool $showAlert = false,
        string $url = '',
        int $cacheTime = 0
    ): array {
        if (mb_strlen($text > 100)) {
            throw new TelegramException('Text of the notification need between 0-200 characters, '.mb_strlen($text > 100).' passed');
        }

        $payload = [
            'callback_query_id' => $callbackQueryId,
            'show_alert'        => $showAlert,
            'cache_time'        => $cacheTime
        ];
        if (!empty($text)) {
            $payload['text'] = $text;
        }

        if (!empty($url)) {
            $payload['url'] = $url;
        }

        return $this->sendRequest('answerCallbackQuery', $payload);
    }

    /**
     * Set reply keyboard
     *
     * @param  array<array>  $rows  Rows of buttons (array of arrays)
     *
     * @return $this
     */
    public function setReplyKeyboardByRows(array $rows): TelegramMessage
    {
        foreach ($rows as $row) {
            $this->addReplyButtonsRow($row);
        }
        return $this;
    }

    public function setReplyKeyboard(array $data): TelegramMessage
    {
        $this->payload['reply_markup'] = $data['reply_markup'] ?? $data;
        return $this;
    }

    /**
     * Add one row of ReplyButtons
     *
     * @param  array<array>  $buttons  For example:
     * [
     *  ['text' => 'Button 1'],
     *  ['text' => 'Button 2'],
     * ]
     *
     * @return $this
     */
    public function addReplyButtonsRow(array $buttons): TelegramMessage
    {
        if (!isset($this->payload['reply_markup'])) {
            $this->payload['reply_markup'] = ['resize_keyboard' => true, 'keyboard' => [$buttons]];
        } else {
            if (is_string($this->payload['reply_markup'])) {
                $this->payload['reply_markup'] = json_decode($this->payload['reply_markup'], true);
            }
            $this->payload['reply_markup']['resize_keyboard'] = true;
            $this->payload['reply_markup']['one_time_keyboard'] = false;
            $this->payload['reply_markup']['keyboard'][] = $buttons;
        }
        return $this;
    }

    /**
     * Clear reply markup rows
     *
     * @return $this
     */
    public function clearReplyMarkupRows(): TelegramMessage
    {
        $this->payload['reply_markup'] = ['keyboard' => []];
        return $this;
    }

    /**
     * Remove keyboard from chat
     *
     * @param  int  $messageId
     * @param  int|null  $chatId
     *
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     * @throws TelegramException
     */
    public function removeReplyMarkup(int $messageId, int $chatId = null): array
    {
        $this->payload['reply_markup'] = ['remove_keyboard' => true];
        return $this->editReplyMarkup($messageId, $chatId);
    }

    /**
     * Use this method to edit only the reply markup of messages. On success, if the edited message
     * is not an inline message, the edited Message is returned, otherwise True is returned.
     *
     * @see https://core.telegram.org/bots/api#editmessagereplymarkup
     *
     * @param  int  $messageId
     * @param  int|null  $chatId
     *
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     * @throws TelegramException
     */
    public function editReplyMarkup(int $messageId, int $chatId = null): array
    {
        //todo: Error 400 Message cannot be edited

        if (empty($this->payload['chat_id']) && empty($chatId)) {
            throw new TelegramException('Chat ID not defined');
        }
        $chatId = $chatId ?? $this->payload['chat_id'];

        return $this->sendRequest('editMessageReplyMarkup', [
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'reply_markup' => json_encode($this->payload['reply_markup'])
        ]);
    }

    /**
     * Sends the message silently. Users will receive a notification with no sound.
     *
     * @return $this
     */
    public function silentMode(): TelegramMessage
    {
        $this->payload['disable_notification'] = true;
        return $this;
    }

    /**
     * Disables link previews for links in this message
     *
     * @return $this
     */
    public function disableWebPagePreview(): TelegramMessage
    {
        $this->payload['disable_web_page_preview'] = true;
        return $this;
    }

    /**
     * Add photo
     *
     * @param  string  $photoUrlOrFileId
     *
     * @return TelegramMessage
     * @throws TelegramException
     */
    public function photo(string $photoUrlOrFileId): TelegramMessage
    {
        $this->isPhoto = true;
        $this->payload['photo'] = $photoUrlOrFileId;
        $this->contentToCaption();
        return $this;
    }

    /**
     * @return void
     * @throws TelegramException
     */
    private function contentToCaption(): void
    {
        if (isset($this->payload['text'])) {
            $this->caption(mb_substr($this->payload['text'], 0, self::MAX_CAPTION_LENGTH));
            unset($this->payload['text']);
        }
    }

    /**
     * Set document text caption
     *
     * @param  string  $caption  0-1024 characters
     *
     * @return $this
     * @throws TelegramException
     */
    public function caption(string $caption): TelegramMessage
    {
        if (empty($caption)) {
            throw new TelegramException('Message content not defined');
        }
        $contentLength = mb_strlen($caption);
        if ($contentLength > self::MAX_CAPTION_LENGTH) {
            throw new TelegramException('Caption text too large. Max: '.self::MAX_MESSAGE_LENGTH.'. Passed: '.$contentLength);
        }
        $this->payload['caption'] = $caption;
        return $this;
    }

    /**
     * Add animation
     *
     * @param  string  $animationUrlOrFileId
     *
     * @return TelegramMessage
     * @throws TelegramException
     */
    public function animation(string $animationUrlOrFileId): TelegramMessage
    {
        $this->isAnimation = true;
        $this->payload['animation'] = $animationUrlOrFileId;
        $this->contentToCaption();
        return $this;
    }

    /**
     * Add document
     *
     * @param  string  $documentUrlOrFileId
     *
     * @return TelegramMessage
     * @throws TelegramException
     */
    public function document(string $documentUrlOrFileId): TelegramMessage
    {
        $this->isDocument = true;
        $this->payload['document'] = $documentUrlOrFileId;
        $this->contentToCaption();
        return $this;
    }

    /**
     * Add video
     *
     * @param  string  $videoUrlOrFileId
     *
     * @return TelegramMessage
     * @throws TelegramException
     */
    public function video(string $videoUrlOrFileId): TelegramMessage
    {
        $this->isVideo = true;
        $this->payload['video'] = $videoUrlOrFileId;
        $this->contentToCaption();
        return $this;
    }

    /**
     * Use this method to send a group of photos, videos, documents or audios as an album. Documents and audio files can be only grouped
     * in an album with messages of the same type. On success, an array of Messages that were sent is returned
     *
     * @see https://core.telegram.org/bots/api#sendmediagroup
     *
     * @param  array  $items  must include 2-10 items
     *
     * @return array
     * @throws TelegramException
     */
    public function sendMediaGroup(array $items): array
    {
        $itemsCount = count($items);
        if ($itemsCount < 2 || $itemsCount > 10) {
            throw new TelegramException("Media Group must include 2-10 items. $itemsCount passed");
        }

        if (isset($this->payload['caption'])) {
            $items[0]['caption'] = $this->payload['caption'];
        }
        $this->payload['media'] = json_encode($items);

        return $this->sendRequest('sendMediaGroup', $this->payload);
    }

    /**
     * @param  array<array>  $buttons
     *
     * @return $this
     */
    public function addInlineButtonRow(array $buttons): TelegramMessage
    {
        if (!isset($this->payload['reply_markup'])) {
            $this->payload['reply_markup'] = ['inline_keyboard' => [$buttons]];
        } else {
            $this->payload['reply_markup']['inline_keyboard'][] = $buttons;
        }
        return $this;
    }

    /**
     * Use this method to send reply
     *
     * @param  int  $messageId  If the message is a reply, ID of the original message
     * @param  bool  $allowSendingWithoutReply  Pass True, if the message should be sent even if the specified replied-to message is not found
     *
     * @return $this
     */
    public function replyTo(int $messageId, bool $allowSendingWithoutReply = true): TelegramMessage
    {
        $this->payload['reply_to_message_id'] = $messageId;
        $this->payload['allow_sending_without_reply'] = $allowSendingWithoutReply;
        return $this;
    }

    /**
     * Get payload
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Get md5 hash of payload
     *
     * @return string
     */
    public function getPayloadHash(): string
    {
       return md5(json_encode($this->payload));
    }

    /**
     * Delete message
     *
     * @see https://core.telegram.org/bots/api#deletemessage
     *
     * @param  int  $chatId
     * @param  int  $messageId
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramBotKickedException
     * @throws Exceptions\TelegramConnectionRefusedException
     * @throws Exceptions\TelegramRequestException
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws GuzzleException
     */
    public function deleteMessage(int $chatId, int $messageId): array
    {
        return $this->sendRequest('deleteMessage', ['chat_id' => $chatId, 'message_id' => $messageId]);
    }

    /**
     * Set message parse mode
     *
     * @param  string  $parseMode
     *
     * @return $this
     */
    public function setParseMode(string $parseMode): static
    {
        $this->payload['parse_mode'] = $parseMode;
        return $this;
    }
}
