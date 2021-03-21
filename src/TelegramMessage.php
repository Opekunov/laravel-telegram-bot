<?php


namespace Opekunov\LaravelTelegramBot\Telegram;


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

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    public function __construct(string $content = '')
    {
        $this->content($content);
        $this->payload['parse_mode'] = 'Markdown';
        parent::__construct();
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

    public function disableNotification()
    {
        $this->payload['disable_notification'] = true;
        return $this;
    }

    public function disableWebPagePreview()
    {
        $this->payload['disable_web_page_preview'] = true;
        return $this;
    }

    /**
     * @param int $chatId
     * @param null $botToken
     * @return \Illuminate\Http\Client\Response
     */
    public function sent(int $chatId, $botToken = null) : \Illuminate\Http\Client\Response
    {
        $this->payload['chat_id'] = $chatId;
        $type = isset($this->payload['photo']) ? 'sendPhoto' : 'sendMessage';
        $type = isset($this->payload['video']) ? 'sendVideo' : $type;

        return !$botToken ? $this->sendRequest($type, $this->payload) : $this->sendRequestWithBotToken($botToken, $type, $this->payload);
    }

    /**
     * @param int $chatId
     * @param string $stickerPath
     * @return \Illuminate\Http\Client\Response
     */
    public function sendSticker(int $chatId, string $stickerPath) : \Illuminate\Http\Client\Response
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

    public function addDefaultEventButtonsRow()
    {
        $buttons = [
            [
                'text' => '👍 1',
                'callback_data' => 'event_1'
            ],
            [
                'text' => '❤️ 3',
                'callback_data' => 'event_3'
            ],
            [
                'text' => '😍️ 5',
                'callback_data' => 'event_5'
            ],
            [
                'text' => '🔥 10',
                'callback_data' => 'event_10'
            ]
        ];
        return $this->addButtonsRow($buttons);
    }

    public function addEventLikesRow(int $stash, int $maxStash)
    {
        $button = [
            [
                "text" => "Собрано $stash / $maxStash ❤️",
                'callback_data' => 'ignore'
            ]
        ];
        return $this->addButtonsRow($button);
    }

    public function sendTypingAction(int $chatId){
        return $this->sendChatAction('typing', $chatId);
    }

    public function sendChatAction(string $action, int $chatId){
        $this->payload = [
            'chat_id' => $chatId,
            'action' => $action
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
