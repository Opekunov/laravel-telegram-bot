<?php


namespace App\Telegram;


class TelegramFiles extends TelegramCore
{
    /**
     * Ссылка на первый аватар пользователя
     * @param null $tgId если не указан Telegram ID пользователя, берется TgID отправителя
     * @return string|null ссылка
     */
    public function getAvatarLink($tgId)
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
        if (!$file = $this->getFile($fileID)) return null;
        return config('telegram.bot-api.base_uri')
            . '/file/bot'
            . config('telegram.bot-api.token')
            . '/' . $file['result']['file_path'] ?? null;
    }
}