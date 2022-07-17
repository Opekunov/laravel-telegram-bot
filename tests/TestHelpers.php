<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Opekunov\LaravelTelegramBot\Entities\Chat;
use Opekunov\LaravelTelegramBot\Entities\Message;
use Opekunov\LaravelTelegramBot\Entities\Update;
use Opekunov\LaravelTelegramBot\Entities\User;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;

/**
 * @link            https://github.com/php-telegram-bot/core
 * @author          Avtandil Kikabidze <akalongman@gmail.com>
 * @copyright       Avtandil Kikabidze <akalongman@gmail.com>
 * @license         http://opensource.org/licenses/mit-license.php  The MIT License (MIT)
 * @package         TelegramTest
 */
class TestHelpers
{
    /**
     * Data template of a user.
     *
     * @var array
     */
    protected static $user_template = [
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
    protected static $chat_template = [
        'id'                             => 1,
        'first_name'                     => 'first',
        'last_name'                      => 'last',
        'username'                       => 'name',
        'type'                           => 'private',
        'all_members_are_administrators' => false,
    ];

    /**
     * Set the value of a private/protected property of an object
     *
     * @param object $object   Object that contains the property
     * @param string $property Name of the property who's value we want to set
     * @param mixed  $value    The value to set to the property
     *
     * @throws \ReflectionException
     */
    public static function setObjectProperty(object $object, string $property, $value): void
    {
        $ref_object   = new \ReflectionObject($object);
        $ref_property = $ref_object->getProperty($property);
        $ref_property->setAccessible(true);
        $ref_property->setValue($object, $value);
    }

    /**
     * Set the value of a private/protected static property of a class
     *
     * @param string $class    Class that contains the static property
     * @param string $property Name of the property who's value we want to set
     * @param mixed  $value    The value to set to the property
     *
     * @throws \ReflectionException
     */
    public static function setStaticProperty(string $class, string $property, $value): void
    {
        $ref_property = new \ReflectionProperty($class, $property);
        $ref_property->setAccessible(true);
        $ref_property->setValue(null, $value);
    }

    /**
     * Return a simple fake Update object
     *
     * @param array $data Pass custom data array if needed
     *
     * @return Update
     */
    public static function getFakeUpdateObject(array $data = []): Update
    {
        $data = [
            'update_id' => mt_rand(),
            ...$data
        ];
        return new Update($data, 'testbot');
    }

    /**
     * Return a fake command object for the passed command text
     *
     * @param string $command_text
     *
     * @return Update
     */
    public static function getFakeUpdateCommandObject(string $command_text): Update
    {
        $data = [
            'update_id' => mt_rand(),
            'message'   => [
                'message_id' => mt_rand(),
                'from'       => self::$user_template,
                'chat'       => self::$chat_template,
                'date'       => time(),
                'text'       => $command_text,
            ],
        ];
        return self::getFakeUpdateObject($data);
    }

    /**
     * Return a fake user object.
     *
     * @param array $data Pass custom data array if needed
     *
     * @return User
     */
    public static function getFakeUserObject(array $data = []): User
    {
        ($data === null) && $data = [];

        return new User($data + self::$user_template);
    }

    /**
     * Return a fake chat object.
     *
     * @param array $data Pass custom data array if needed
     *
     * @return Chat
     */
    public static function getFakeChatObject(array $data = []): Chat
    {
        return new Chat($data + self::$chat_template);
    }

    /**
     * Get fake recorded audio track
     *
     * @return array
     */
    public static function getFakeRecordedAudio(): array
    {
        $mime_type = ['audio/ogg', 'audio/mpeg', 'audio/vnd.wave', 'audio/x-ms-wma', 'audio/basic'];
        return [
            'file_id'   => mt_rand(1, 999),
            'duration'  => mt_rand(1, 99) . ':' . mt_rand(1, 60),
            'performer' => 'phpunit',
            'title'     => 'track from phpunit',
            'mime_type' => $mime_type[array_rand($mime_type, 1)],
            'file_size' => mt_rand(1, 99999),
        ];
    }

    /**
     * Return a fake message object using the passed ids.
     *
     * @param array $message_data Pass custom message data array if needed
     * @param array $user_data    Pass custom user data array if needed
     * @param array $chat_data    Pass custom chat data array if needed
     *
     * @return Message
     */
    public static function getFakeMessageObject(array $message_data = [], array $user_data = [], array $chat_data = []): Message
    {
        return new Message($message_data + [
                'message_id' => mt_rand(),
                'from'       => $user_data + self::$user_template,
                'chat'       => $chat_data + self::$chat_template,
                'date'       => time(),
                'text'       => 'dummy',
            ], 'testbot');
    }
}
