<?php

namespace Opekunov\LaravelTelegramBot\Telegram;

use Illuminate\Support\Str;

class Telegram extends TelegramCore
{
    protected $_botUri;
    protected $_baseUri;
    private $_response;
    private $_editedMessage = false;

    /**
     * Telegram constructor.
     * @param array $_response
     */
    public function __construct(array $_response)
    {
        $this->_response = $_response ?? ['message' => ['']];
        $this->_editedMessage = isset($_response['edited_message']);
        parent::__construct();
    }

    /**
     * Если есть reply, возвращает его
     * @return Telegram|null
     */
    public function getReplyMessage()
    {
        $message = $this->getMessage();
        if (!isset($message['reply_to_message'])) return null;
        return new self(["message" => $message['reply_to_message']]) ?? null;
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
     * Возвращает количетсво символов в строке
     * @return int
     */
    public function getTextLength()
    {
        return Str::length($this->getText());
    }

    /**
     * Приватный чат или нет
     * @return bool
     */
    public function isPrivateChat()
    {
        return $this->getType() == 'private';
    }

    public function isPublicChat()
    {
        return $this->getType() == 'group';
    }

    public function isInlineQuery()
    {
        return $this->_response['inline_query'] ?? null;
    }

    public function isCallbackQuery()
    {
        return $this->_response['callback_query'] ?? null;
    }

    public function getCallbackQueryData()
    {
        $msg = $this->getMessage();
        return $msg['data'] ?? null;
    }

    /**
     * Команда /start или нет
     * @return bool
     */
    public function isStartCommand()
    {
        return $this->findCommandByName('/start') ? true : false;
    }

    public function getInlineQueryObject(){
        return $this->_response['inline_query'] ?? null;
    }

    public function getInlineQuery(){
        if(!$query = $this->getInlineQueryObject()) return null;
        return $query['query'] ?? null;
    }

    public function getChosenInlineResult()
    {
        return $this->_response['chosen_inline_result'] ?? null;
    }

    public function getInlineQueryLength()
    {
        return Str::length($this->getInlineQuery());
    }

    public function getInlineQueryId(){
        if(!$query = $this->getInlineQueryObject()) return null;
        return $query['id'] ?? null;
    }

    /**
     * Поиск строки в тексте сообщения
     * @param $needle
     * @param null $haystack
     * @return bool
     */
    public function findStrInMessage($needle, $haystack = null)
    {
        return mb_stripos($haystack ?? $this->getText(), $needle) !== false;
    }

    /**
     * Ссылка на первый аватар пользователя
     * @param null $tgId если не указан Telegram ID пользователя, берется TgID отправителя
     * @return string|null ссылка
     */
    public function getAvatarLink($tgId = null)
    {
        $avatars = $this->sendRequest('getUserProfilePhotos', [
            'user_id' => $tgId ?? $this->getUserId(),
            'limit' => 1
        ]);
        if (!$avatars['result']['total_count'] > 0) return null;
        $avatar = current($avatars['result']['photos']);
        $avatarId = end($avatar)['file_id'];

        return $this->getFileLink($avatarId);
    }

    /**
     * Get File Telegram
     * @param $fileID
     * @return array
     */
    public function getFile($fileID)
    {
        return $this->sendRequest('getFile', ['file_id' => $fileID]);
    }

    /**
     * Финальная ссылка на файл TG
     * @param $fileID
     * @return string|null
     */
    public function getFileLink($fileID)
    {
        if(!$file = $this->getFile($fileID)) return null;
        return $this->_baseUri . '/file' . $this->_botUri . $file['result']['file_path'] ?? null;
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
    public function findCommandByName(string $command)
    {
        if (!$commands = $this->getCommands()) return null;
        foreach ($commands as $com) {
            if ($com['text'] == $command)
                return $com;
        }
        return null;
    }

    public function entitySubstr($entity, $removeFirstSymbol = false)
    {
        $add = $removeFirstSymbol ? 1 : 0;
        return Str::substr($this->getText(), $entity['offset'] + $add, $entity['length'] - $add);
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
    public function getEntitiesByType(string $type, bool $addText = false, bool $removeFirstSymbol = false)
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
     * Отредактрованное сообщение или нет
     * @return bool
     */
    public function isEditedMessage(): bool
    {
        return $this->_editedMessage;
    }
}
