<?php

namespace Opekunov\LaravelTelegramBot\Utilities;

use Opekunov\LaravelTelegramBot\Entities\Message;
use Opekunov\LaravelTelegramBot\Entities\Update;

trait Faker
{

    /**
     * Data template of a user.
     *
     * @var array
     */
    protected array $userTemplate = [
        'id'         => 1,
        'first_name' => 'first',
        'last_name'  => 'last',
        'username'   => 'user',
    ];

    /**
     * Data template of a chat.
     *
     * @var array
     */
    protected array $chatTemplate = [
        'id'                             => 1,
        'first_name'                     => 'first',
        'last_name'                      => 'last',
        'username'                       => 'name',
        'type'                           => 'private',
        'all_members_are_administrators' => false,
    ];

    /**
     * Return a fake message object using the passed ids.
     *
     * @param  array  $message_data  Pass custom message data array if needed
     * @param  array  $user_data  Pass custom user data array if needed
     * @param  array  $chat_data  Pass custom chat data array if needed
     *
     * @return Message
     */
    public function getFakeMessageObject(array $message_data = [], array $user_data = [], array $chat_data = []): Message
    {
        return new Message(
            $message_data + [
                'message_id' => mt_rand(),
                'from'       => $user_data + $this->userTemplate,
                'chat'       => $chat_data + $this->chatTemplate,
                'date'       => time(),
                'text'       => 'dummy',
            ], 'testbot'
        );
    }

    /**
     * Return a simple fake Update object
     *
     * @param  array  $data  Pass custom data array if needed
     *
     * @return Update
     */
    public function getFakeUpdateObject(array $data = []): Update
    {
        $data = [
            'update_id' => mt_rand(),
            ...$data
        ];
        return new Update($data, 'testbot');
    }
}
