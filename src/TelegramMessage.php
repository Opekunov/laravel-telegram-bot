<?php


namespace Opekunov\LaravelTelegramBot;


class TelegramMessage extends TelegramCore
{

    /** @var array Params payload. */
    protected $payload = [];

    /** @var array Inline Keyboard Buttons. */
    protected $buttons = [];

    public function getButtons()
    {
        return $this->payload['reply_markup']['inline_keyboard'];
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public function __construct(string $apikey, string $content = '')
    {
        $this->content($content);
        $this->payload['parse_mode'] = 'MarkdownV2';
        parent::__construct($apikey);
    }

    public function content(string $content): self
    {
        $this->payload['text'] = $content;
        return $this;
    }

    public function photo(string $webPath, string $caption = null): self
    {
        $this->payload['photo'] = $webPath;
        if($caption)
            $this->payload['caption'] = $caption;
        return $this;
    }

    public function video(string $webPath, string $caption = null): self
    {
        $this->payload['video'] = $webPath;
        if($caption)
            $this->payload['caption'] = $caption;
        return $this;
    }

    public function replyTo(int $messageId)
    {
        $this->payload['reply_to_message_id'] = $messageId;
        return $this;
    }


    /**
     * @param  int  $chatId
     * @param  string|null  $botToken
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramRequestException
     */
    public function send(int $chatId, string $botToken = null): array
    {
        $this->payload['chat_id'] = $chatId;
        $type = isset($this->payload['photo']) ? 'sendPhoto' : 'sendMessage';
        $type = isset($this->payload['video']) ? 'sendVideo' : $type;

        if($this->payload['parse_mode'] === 'MarkdownV2')
        {
            $this->payload['text'] = $this->escapeMarkdown($this->payload['text']);
        }

        return !$botToken ? $this->sendRequest($type, $this->payload) : $this->sendRequestWithBotToken($botToken, $type, $this->payload);
    }

    /**
     * Sends the message silently. Users will receive a notification with no sound.
     * @return $this
     */
    public function silentMode(): TelegramMessage
    {
        $this->payload['disable_notification'] = true;
        return $this;
    }

    /**
     * Disables link previews for links in this message
     * @return $this
     */
    public function disableWebPagePreview(): TelegramMessage
    {
        $this->payload['disable_web_page_preview'] = true;
        return $this;
    }

    /**
     * Disable parse mode
     * @return $this
     */
    public function disableParseMode(){
        unset($this->payload['parse_mode']);
        return $this;
    }

    /**
     * MarkdownV2 style
     * @see https://core.telegram.org/bots/api#markdownv2-style
     * @return $this
     */
    public function setMarkdownV2ParseMode(): TelegramMessage
    {
        $this->payload['parse_mode'] = 'MarkdownV2';
        return $this;
    }

    private function escapeMarkdown(string $string)
    {
        return preg_replace('/[_*\[\]()~`>#+-=|{}.!]{1}/is', '\\$0', $string);
    }

    /**
     * HTML Style
     * https://core.telegram.org/bots/api#html-style
     * @return $this
     */
    public function setHTMLParseMode(): TelegramMessage
    {
        $this->payload['parse_mode'] = 'html';
        return $this;
    }


    /**
     * @param  int  $chatId
     * @param  string  $stickerPath
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramRequestException
     */
    public function sendSticker(int $chatId, string $stickerPath): array
    {
        $this->payload['chat_id'] = $chatId;
        $this->payload['sticker'] = $stickerPath;
        $type = 'sendSticker';

        return $this->sendRequest($type, $this->payload);
    }

    public function addButtonsRow(array $buttons)
    {
        //inline_keyboard
        if (!isset($this->payload['reply_markup']))
            $this->payload['reply_markup'] = ['inline_keyboard' => [$buttons]];
        else
            $this->payload['reply_markup']['inline_keyboard'][] = $buttons;
        return $this;
    }

    public function addReplyButtonsRow(array $buttons)
    {
        //inline_keyboard
        if (!isset($this->payload['reply_markup']))
            $this->payload['reply_markup'] = ['resize_keyboard' => true, 'keyboard' => [$buttons]];
        else {
            $this->payload['reply_markup']['resize_keyboard'] = true;
            $this->payload['reply_markup']['keyboard'][] = $buttons;
        }
        return $this;
    }

    public function addDefaultReplyKeyboard()
    {
        return $this->addReplyButtonsRow([
            ['text' => 'Мои баллы'],
            ['text' => 'Баллы команды'],
        ])
            ->addReplyButtonsRow([
                [
                    'text' => 'Перейти на сайт',
                    ]
            ]);
    }

    public function sendTypingAction(int $chatId){
        return $this->sendChatAction('typing', $chatId);
    }

    public function sendChatAction(string $action, int $chatId)
    {
        $this->payload = [
            'chat_id' => $chatId,
            'action'  => $action
        ];
        return $this->sendRequest('sendChatAction', $this->payload);
    }

    public function updateButtons(int $messageId, int $chatId)
    {
        $this->payload = [
            'reply_markup' => $this->payload['reply_markup'],
            'chat_id' => $chatId,
            'message_id' => $messageId
        ];
        return $this->sendRequest('editMessageReplyMarkup', $this->payload);
    }

    /**
     * Используйте этот метод для отправки ответов на запросы обратного вызова, отправленные с встроенных клавиатур.
     * Ответ будет отображаться пользователю в виде уведомления в верхней части экрана чата или в виде предупреждения.
     * @param string $callbackQueryId Уникальный идентификатор ответа на запрос
     * @param string $text Текст уведомления. Если не указан, пользователю ничего не будет показано, 0-200 символов
     * @param int $cacheTime Максимальное время в секундах, в течение которого результат запроса обратного вызова может кэшироваться на стороне клиента. По умолчанию 0.
     * @param bool $showAlert Если это true, Telegram будет показывать предупреждение вместо уведомления в верхней части экрана чата. По умолчанию false.
     * @return array
     */
    public function sentAnswerCallbackQuery(string $callbackQueryId, string $text, int $cacheTime = 0, bool $showAlert = true)
    {
        $this->payload = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
            'cache_time' => $cacheTime
        ];

        return $this->sendRequest('answerCallbackQuery', $this->payload);
    }

}
