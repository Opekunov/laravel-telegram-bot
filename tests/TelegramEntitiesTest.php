<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

use PHPUnit\Framework\TestCase;

class TelegramEntitiesTest extends TestCase
{
    public function testHelperMessageTypes()
    {
        $types = [
            'text',
            'audio',
            'animation',
            'document',
            'game',
            'photo',
            'sticker',
            'video',
            'voice',
            'video_note',
            'contact',
            'location',
            'venue',
            'poll',
            'new_chat_members',
            'left_chat_member',
            'new_chat_title',
            'new_chat_photo',
            'delete_chat_photo',
            'group_chat_created',
            'supergroup_chat_created',
            'channel_chat_created',
            'message_auto_delete_timer_changed',
            'migrate_to_chat_id',
            'migrate_from_chat_id',
            'pinned_message',
            'invoice',
            'successful_payment',
            'passport_data',
            'proximity_alert_triggered',
            'video_chat_scheduled',
            'video_chat_started',
            'video_chat_ended',
            'video_chat_participants_invited',
            'web_app_data',
            'reply_markup',
        ];

        $types = array_map(function ($v){ return $this->dashesToCamelCase($v); }, $types);
        foreach ($types as $type) {
            echo " * @method MessageReceiver setUp{$type}Action(string|callable \$func) Sets the function or Action::class (with __invoke()) that will be called if the update type is $type. First argument is $type class, second argument is Handler class\r\n";
        }

        $this->assertTrue(true);
    }

    function dashesToCamelCase($string)
    {
        return implode('', array_map('ucfirst', explode('_', $string)));
    }

    public function testSendCallback()
    {
        \Opekunov\LaravelTelegramBot\TelegramMessage::init('5595935257:AAHCq3J-id7Nj5cTcDpdkmnc5pIpXA4ZlNg')
            ->addInlineButtonRow([\Opekunov\LaravelTelegramBot\TelegramMessage::inlineButton('123', callbackData: 'test')])
            ->content('test')
            ->send(-343237347);
    }

    public function testSendLocation()
    {
        \Opekunov\LaravelTelegramBot\TelegramMessage::init('5595935257:AAHCq3J-id7Nj5cTcDpdkmnc5pIpXA4ZlNg')
            ->addReplyButtonsRow([['text' => 'Click', 'request_location' => true]])
            ->content('test')
            ->send(5404263648);
    }

    public function testSendPhone()
    {
        \Opekunov\LaravelTelegramBot\TelegramMessage::init('5595935257:AAHCq3J-id7Nj5cTcDpdkmnc5pIpXA4ZlNg')
            ->addReplyButtonsRow([['text' => 'Click', 'request_contact' => true]])
            ->setOneTimeKeyboard()
            ->content('test')
            ->send(5404263648);
    }

}
