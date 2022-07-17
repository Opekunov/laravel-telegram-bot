<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

namespace Receivers;

use Opekunov\LaravelTelegramBot\Entities\Message;
use Opekunov\LaravelTelegramBot\Entities\Update;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;
use Opekunov\LaravelTelegramBot\Handler;
use Opekunov\LaravelTelegramBot\Receivers\UpdateReceiver;
use PHPUnit\Framework\TestCase;

class UpdateReceiverTest extends TestCase
{

    public function testSetUpChannelPostAction()
    {

    }

    public function testSetUpEditedMessageAction()
    {
    }

    public function testSetUpPreExecuteAction()
    {
    }

    public function testSetUpPoolAnswerAction()
    {
    }

    public function testSetUpPreCheckoutQueryAction()
    {
    }

    public function testSetUpChatMemberAction()
    {
    }

    public function testSetUpPoolAction()
    {
    }

    public function testSetUpInlineQueryAction()
    {
    }

    public function testSetUpChosenInlineResultAction()
    {
    }

    public function testSetUpMessageAction()
    {
        $jsonFile = __DIR__.'/../examples_json/message.json';
        $testData = file_get_contents($jsonFile);
        $handler = new Handler($testData);
        $messageId = $handler->getUpdate()->getMessage()->getMessageId();

        $receiver = new UpdateReceiver($handler);
        $receiver->setUpMessageAction(fn(Message $message) => $this->assertEquals($message->getMessageId(), $messageId))
            ->execute();
    }

    public function testSetUpCallbackQueryAction()
    {
    }

    public function test__call()
    {
        $jsonFile = __DIR__.'/../examples_json/message.json';
        $testData = file_get_contents($jsonFile);

        $handler = new Handler($testData);
        $receiver = new UpdateReceiver($handler);
        $preExecute = null;
        $receiver
            ->setUpPreExecuteAction(function (Handler $handler) use (&$preExecute) {
                $preExecute = $handler->getUpdateId();
            })
            ->execute();

        $this->assertEquals($preExecute, $handler->getUpdateId());

        $this->expectException(\TypeError::class);
        $receiver->setUpPreExecuteAction('');
    }

    public function testSetUpMyChatMemberAction()
    {
    }

    public function testExecute()
    {
    }

    public function testSetUpChatJoinRequestAction()
    {
    }

    public function testSetUpEditedChannelPostAction()
    {
    }

    public function testSetUpShippingQueryAction()
    {
    }
}
