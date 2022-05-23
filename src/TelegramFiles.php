<?php


namespace Opekunov\LaravelTelegramBot;


use TelegramRequestException;

class TelegramFiles extends TelegramCore
{
    /**
     * Ссылка на первый аватар пользователя
     *
     * @param  int  $tgId  если не указан Telegram ID пользователя, берется Telegram ID отправителя
     *
     * @return string|null ссылка
     * @throws TelegramRequestException
     */
    public function getAvatarLink(int $tgId): ?string
    {
        $avatars = $this->sendRequest('getUserProfilePhotos', [
            'user_id' => $tgId,
            'limit' => 1
        ]);
        if (!$avatars['result']['total_count'] > 0) return null;
        $avatar = current($avatars['result']['photos']);
        $avatarId = end($avatar)['file_id'];

        return $this->getFileLink($avatarId);
    }

    /**
     * Get File Telegram
     *
     * @param  string  $fileID
     *
     * @return array
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramRequestException
     */
    public function getFile(string $fileID): array
    {
        return $this->sendRequest('getFile', ['file_id' => $fileID]);
    }

    /**
     * Финальная ссылка на файл TG
     *
     * @param  string  $fileID
     *
     * @return string|null
     * @throws Exceptions\TelegramBadTokenException
     * @throws Exceptions\TelegramRequestException
     */
    public function getFileLink(string $fileID): ?string
    {
        if (!$file = $this->getFile($fileID)) return null;
        return $this->_baseUri
            . '/file/bot'
            . $this->_botToken
            . '/' . $file['file_path'] ?? null;
    }
}
