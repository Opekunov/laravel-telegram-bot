<?php


namespace Opekunov\LaravelTelegramBot;


class TelegramFiles extends Telegram
{
    /**
     * Get URL for first user profile photo
     *
     * @param  int  $userID  Unique identifier of the target user
     * @param  bool  $usePlaceholder If True and user hasn't photo return 500px placeholder https://via.placeholder.com/500
     *
     * @return string|null Profile url or placeholder or null
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    public function getFirstUserProfilePhoto(int $userID, bool $usePlaceholder = true): ?string
    {
        $photos = $this->getUserProfilePhotos($userID, 1);

        if (!$photos['total_count'] > 0) {
            return $usePlaceholder ? 'https://via.placeholder.com/500' : null;
        }

        $photo = current($photos['photos']);
        $avatarFileId = end($photo)['file_id'];

        return $this->getFileLink($avatarFileId);
    }

    /**
     * Get download link for file
     *
     * @param  string  $fileID
     *
     * @return string|null
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    public function getFileLink(string $fileID): ?string
    {
        if (!$file = $this->getFile($fileID)) {
            return null;
        }
        return $this->baseApiUri
            .'/file/bot'
            .$this->botToken
            .'/'.$file['file_path'] ?? null;
    }

    /**
     * Get File Telegram
     *
     * @param  string  $fileID
     *
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    public function getFile(string $fileID): array
    {
        return $this->sendRequest('getFile', ['file_id' => $fileID]);
    }

    /**
     * Use this method to get a list of profile pictures for a user. Returns a UserProfilePhotos object
     *
     * @param  int  $userId  Unique identifier of the target user
     * @param  int|null  $limit  Limits the number of photos to be retrieved. Values between 1-100 are accepted. Defaults to 100
     * @param  int|null  $offset  Sequential number of the first photo to be returned. By default, all photos are returned
     *
     * @return array
     * @throws Exceptions\TelegramTooManyRequestsException
     * @throws Exceptions\TelegramRequestException
     */
    public function getUserProfilePhotos(int $userId, int $limit = null, int $offset = null): array
    {
        $data = ['user_id' => $userId];
        if($limit) $data['limit'] = $limit;
        if($offset) $data['offset'] = $offset;

        return $this->sendRequest('getUserProfilePhotos', $data);
    }
}
