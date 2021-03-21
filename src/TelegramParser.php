<?php


namespace App\Telegram;


use Illuminate\Support\Str;

class TelegramParser
{
    private $_response;

    /**
     * Telegram parser constructor.
     * @param array $_response
     */
    public function __construct(array $_response)
    {
        $this->_response = $_response ?? ['message' => ['']];
    }

    /**
     * Приватный чат или нет
     * @return bool
     */
    public function isPrivateChat()
    {
        \Log::info('', ['$this->getType()' => $this->getType(), '$this->getChat()' => $this->getChat()]);
        return $this->getType() == 'private';
    }

    /**
     * Публичный чат или нет
     * @return bool
     */
    public function isPublicChat()
    {
        return $this->getType() == 'group' || $this->getType() == 'supergroup';
    }

    /**
     * Inline query или нет
     * @return bool
     */
    public function isInlineQuery()
    {
        return isset($this->_response['inline_query']);
    }

    /**
     * Callback query или нет
     * @return bool
     */
    public function isCallbackQuery()
    {
        return isset($this->_response['callback_query']);
    }

    /**
     * Отредактированное сообщение или нет
     */
    public function isUpdatedMessage()
    {
        return isset($this->_response['edited_message']);
    }

    /**
     * Выбранный inline query
     * @return bool
     */
    public function isChosenInlineQuery()
    {
        return isset($this->_response['chosen_inline_result']);
    }

    /**
     * Возвращает выбранный ID
     * @return string|null
     */
    public function getChosenInlineResultId()
    {
        if(!$this->isChosenInlineQuery()) return null;
        return $this->_response['chosen_inline_result']['result_id'] ?? null;
    }

    /**
     * Все сообщение
     * @return mixed
     */
    public function getMessage()
    {
        return $this->_response['message'] ??
            $this->_response['edited_message'] ??
            $this->_response['inline_query'] ??
            $this->_response['chosen_inline_result'] ??
            $this->_response['callback_query'] ??
            null;
    }

    /**
     * ID сообщения
     * @return int|null
     */
    public function getMessageId()
    {
        $message = $this->getMessage();
        return $message['message_id'] ?? null;
    }

    /**
     * Если есть reply, возвращает его
     * @return TelegramParser|null
     */
    public function getReplyMessage()
    {
        $message = $this->getMessage();
        if (!isset($message['reply_to_message'])) return null;
        return new self(["message" => $message['reply_to_message']]) ?? null;
    }

    /**
     * Команда /start или нет
     * @return bool
     */
    public function isStartCommand()
    {
        return $this->findCommandByName('/start') ? true : false;
    }

    /**
     * Код регистрации/валидации
     * @return bool
     */
    public function isRegistration()
    {
        return $this->findStrInMessage('reg') && $this->getTextLength() == 8;
    }

    /**
     * Возвращает callback query DATA
     * @return string|null
     */
    public function getCallbackQueryData()
    {
        $msg = $this->getMessage();
        return $msg['data'] ?? null;
    }

    /**
     * Возвращает callback query ID
     * @return string
     */
    public function getCallbackQueryId()
    {
        return $this->getMessage()['id'] ?? null;
    }

    /**
     * Возвращает всю информацию Inline Query
     * @return array|null
     */
    public function getInlineQueryObject(){
        return $this->_response['inline_query'] ?? null;
    }

    /**
     * Возвращает текст Inline запроса
     * @return string|null
     */
    public function getInlineQuery(){
        if(!$query = $this->getInlineQueryObject()) return null;
        return $query['query'] ?? null;
    }

    /**
     * Длина Inline Query
     * @return int
     */
    public function getInlineQueryLength()
    {
        return Str::length($this->getInlineQuery());
    }

    /**
     * ID Inline Query сообщения
     * @return string|null
     */
    public function getInlineQueryId(){
        if(!$query = $this->getInlineQueryObject()) return null;
        return $query['id'] ?? null;
    }

    /**
     * Вовзращает все бот команды
     * @return array|null
     */
    public function getCommands()
    {
        return $this->getEntitiesByType('bot_command', true) ?? null;
    }

    /**
     * Находит первое вхождение команды
     * @param string $command обязательно с слэшэм /str
     * @return array|null
     */
    private function findCommandByName(string $command)
    {
        if (!$commands = $this->getCommands()) return null;
        foreach ($commands as $com) {
            if ($com['text'] == $command)
                return $com;
        }
        return null;
    }

    /**
     * Возвращает ID чата
     * @return int|null
     */
    public function getChatId()
    {
        $chat = $this->getChat();
        return $chat['id'] ?? null;
    }

    /**
     * Title чата
     * @return string|null
     */
    public function getChatTitle()
    {
        $chat = $this->getChat();
        return $chat['title'] ?? null;
    }

    /**
     * Возвращает информацию о чате
     * @return mixed
     */
    public function getChat()
    {
        return $this->getMessage()['chat'] ?? null;
    }

    /**
     * Возвращает полную информацию об отправителе
     * @param bool $withoutIsBot удалить is_bot
     * @param bool $addChatID добавить ID чата
     * @return array|null
     */
    public function getFrom($withoutIsBot = false, $addChatID = false)
    {
        if (!$from = $this->getMessage()['from']) return null;
        if ($withoutIsBot) unset($from['is_bot']);
        if($addChatID)
            $from['chat_id'] = $this->getChatId();

        return $from;
    }

    /**
     * Тип чата
     * @return string|null
     */
    public function getType()
    {
        $chat = $this->getChat();
        return $chat['type'] ?? null;
    }

    /**
     * Telegram ID отправителя
     * @return int|null
     */
    public function getUserId()
    {
        $from = $this->getFrom();
        return $from['id'] ?? null;
    }

    /**
     * Вхождения в сообщение
     * @return array|null
     */
    public function getEntities()
    {
        return $this->getMessage()['entities'] ?? $this->getMessage()['caption_entities'] ?? null;
    }

    /**
     * Упоминания пользователей
     * @param bool $addUsername Добавить username из текста
     * @param bool $withoutAtSymbol Удалить символ @ или нет
     * @return array|null
     */
    public function getMentions($addUsername = true, $withoutAtSymbol = true)
    {
        return $this->getEntitiesByType('mention', $addUsername, $withoutAtSymbol);
    }

    /**
     * Возвращает первое упоминание пользователя
     * @param bool $addUsername Добавить username из текста
     * @param bool $withoutAtSymbol Удалить символ @ или нет
     * @return array|null
     */
    public function getFirstMention($addUsername = true, $withoutAtSymbol = true)
    {
        $mentions = $this->getMentions($addUsername, $withoutAtSymbol);
        return $mentions ? $mentions[0] : null;
    }

    /**
     * Возвращает первое текстовое упоминание пользователя
     * @return array|null
     */
    public function getFirstTextMention()
    {
        $mentions = $this->getTextMentions();
        return $mentions ? $mentions[0] : null;
    }

    /**
     * Имя первого упомянутого пользователя
     * @return string|null
     */
    public function getFirstMentionUsername()
    {
        $mention = $this->getFirstMention(true, true);
        return $mention['text'] ?? null;
    }

    /**
     * ID первого текстово упомянутого пользователя
     * @return string|null
     */
    public function getFirstTextMentionUserId()
    {
        $mention = $this->getFirstTextMention();
        return $mention['user']['id'] ?? null;
    }

    /**
     * Текстовые упоминания пользователей
     * @return array|null
     */
    public function getTextMentions()
    {
        return $this->getEntitiesByType('text_mention');
    }

    /**
     * Находит вхождения по типу
     * @param string $type
     * @param bool $addText //Добавить текст к вхожденям
     * @param bool $removeFirstSymbol //Удалить первый символ из текста
     * @return array|null
     */
    private function getEntitiesByType(string $type, bool $addText = false, bool $removeFirstSymbol = false)
    {
        $entities = $this->getEntities();
        if (!$entities) return null;
        $result = array();
        foreach ($entities as $entity) {
            if ($entity['type'] == $type) {
                $add = $entity;
                if ($addText && isset($entity['offset']))
                    $add['text'] = $this->entitySubstr($entity, $removeFirstSymbol);
                $result[] = $add;
            }
        }
        return $result;
    }

    /**
     * Возвращает текст вхождения
     * @param $entity
     * @param bool $removeFirstSymbol
     * @return string
     */
    private function entitySubstr($entity, $removeFirstSymbol = false)
    {
        $add = $removeFirstSymbol ? 1 : 0;
        return Str::substr($this->getText(), $entity['offset'] + $add, $entity['length'] - $add);
    }

    /**
     * Дата отправки сообщения
     * @return string|null
     */
    public function getDate()
    {
        return $this->getMessage()['date'] ?? null;
    }

    /**
     * Возвращает текст сообщения
     * @return mixed
     */
    public function getText()
    {
        return $this->getMessage()['text'] ?? $this->getMessage()['caption'] ?? null;
    }

    /**
     * Отправитель бот или нет
     * @param bool $acceptConfigBot Пропускать бота из конфига
     * @return bool
     */
    public function fromBot($acceptConfigBot = false)
    {
        if(!isset($this->getFrom()['is_bot'])) return false;
        $isBot = $this->getFrom()['is_bot'];
        if(!$acceptConfigBot) return $isBot;
        elseif($isBot && $this->getUsername() == config('services.telegram.bot_name'))
            return false;
        else return $isBot;
    }

    /**
     * Username пользователя
     * @return string|null
     */
    public function getUsername()
    {
        $from = $this->getFrom();
        return $from['username'] ?? null;
    }

    /**
     * Полное имя пользователя
     * @return string
     */
    public function getUserFullname()
    {
        $from = $this->getFrom();
        return ($from['last_name'] ?? null) . ' ' . ($from['first_name'] ?? null);
    }

    /**
     * Количество символов <= .env MAX_MESSAGE_LENGTH
     * @return bool
     */
    public function checkTextLength()
    {
        return $this->getTextLength() <= config('services.telegram.max_message_length');
    }

    /**
     * Возвращает количество символов в строке
     * @return int
     */
    public function getTextLength()
    {
        return Str::length($this->getText());
    }

    /**
     * Поиск строки в тексте сообщения
     * @param $needle
     * @param null $haystack
     * @return bool
     */
    private function findStrInMessage($needle, $haystack = null)
    {
        return mb_stripos($haystack ?? $this->getText(), $needle) !== false;
    }


}
