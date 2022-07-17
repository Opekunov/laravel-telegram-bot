<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

namespace Receivers;

use Opekunov\LaravelTelegramBot\Actions\TestAction;
use Opekunov\LaravelTelegramBot\Entities\ChannelPost;
use Opekunov\LaravelTelegramBot\Entities\EditedMessage;
use Opekunov\LaravelTelegramBot\Entities\Message;
use Opekunov\LaravelTelegramBot\Entities\PollAnswer;
use Opekunov\LaravelTelegramBot\Handler;
use Opekunov\LaravelTelegramBot\Receivers\UpdateReceiver;
use Opekunov\LaravelTelegramBot\Utilities\Faker;
use PHPUnit\Framework\TestCase;

class UpdateReceiverTest extends TestCase
{
    use Faker;

    public function testSetUpChannelPostAction()
    {
        $message = $this->getFakeMessageObject();
        $channel_post = new ChannelPost($message->jsonSerialize());
        $handler = new Handler($this->getFakeUpdateObject(compact('channel_post')));
        $messageId = $handler->getUpdate()->getChannelPost()->getMessageId();

        $receiver = new UpdateReceiver($handler);
        $receiver->setUpChannelPostAction(fn(ChannelPost $channelPost) => $this->assertEquals($channelPost->getMessageId(), $messageId))
            ->execute();
    }

    public function testSetUpEditedMessageAction()
    {
        $message = $this->getFakeMessageObject();
        $edited_message = new EditedMessage($message->jsonSerialize());
        $handler = new Handler($this->getFakeUpdateObject(compact('edited_message')));
        $messageId = $handler->getUpdate()->getEditedMessage()->getMessageId();

        $receiver = new UpdateReceiver($handler);
        $receiver
            ->setUpEditedMessageAction(fn(EditedMessage $editedMessage) => $this->assertEquals($editedMessage->getMessageId(), $messageId))
            ->execute();
    }

    public function testSetUpPreExecuteAction()
    {
        $receiver = new UpdateReceiver(new Handler($this->getFakeUpdateObject($this->getFakeMessageObject()->jsonSerialize())));
        $receiver
            ->setUpPreExecuteAction(fn() => $this->assertTrue(true))
            ->execute();
    }

    public function testSetUpPollAnswerAction()
    {
        $poll_answer = new PollAnswer(['user' => $this->userTemplate, 'pool_id' => mt_rand(), 'option_id' => mt_rand()]);
        $handler = new Handler($this->getFakeUpdateObject(compact('poll_answer')));
        $option = $poll_answer->getOptionIds();

        $receiver = new UpdateReceiver($handler);
        $receiver
            ->setUpPollAnswerAction(fn(PollAnswer $pollAnswer) => $this->assertEquals($pollAnswer->getOptionIds(), $option))
            ->execute();
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
        $message = $this->getFakeMessageObject();
        $handler = new Handler($this->getFakeUpdateObject(compact('message')));
        $messageId = $handler->getUpdate()->getMessage()->getMessageId();

        $receiver = new UpdateReceiver($handler);
        $receiver->setUpMessageAction(fn(Message $message) => $this->assertEquals($message->getMessageId(), $messageId))
            ->execute();

        $receiver = new UpdateReceiver($handler);
        $receiver
            ->setUpMessageAction(TestAction::class)
            ->execute();
    }

    public function testAddFilters()
    {
        $message = $this->getFakeMessageObject();
        $handler = new Handler($this->getFakeUpdateObject(compact('message')));

        $receiver = new UpdateReceiver($handler);
        $receiver
            ->addFilter(fn() => false)
            ->addFilter(fn() => true)
            ->setUpMessageAction(fn(Message $message) => throw new \Exception('Oops'))
            ->execute();
        $this->assertTrue(true);

        $this->expectException(\Exception::class);
        $receiver = new UpdateReceiver($handler);
        $receiver
            ->addFilter(fn() => true)
            ->addFilter(fn() => true)
            ->setUpMessageAction(fn(Message $message) => throw new \Exception('Oops'))
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
