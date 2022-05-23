<?php

use Dotenv\Dotenv;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;
use Opekunov\LaravelTelegramBot\TelegramMessage;
use PHPUnit\Framework\TestCase;

class TelegramMessageTests extends TestCase
{
    public TelegramMessage $message;

    /** @test */
    public function setEmptyContent()
    {
        $this->expectException(TelegramException::class);
        $this->message->content('');
    }

    /** @test */
    public function setLargeContent()
    {
        $this->expectException(TelegramException::class);
        $this->message->content($this->generateRandomString(4097));
    }

    private function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /** @test */
    public function setLargeCaption()
    {
        $this->expectException(TelegramException::class);
        $this->message->caption($this->generateRandomString(1025));
    }

    /** @test */
    public function sendMessage()
    {
        $res = $this->message->content($this->generateRandomString(20))->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    /** @test */
    public function sendMessageWithEntities()
    {
        $res = $this->message->content('*test* *1* **bold** __opa__ !<>')->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    /** @test */
    public function sendMessageWithReplyKeyboard()
    {
        $row1 = [['text' => 'Test 44'], ['text' => 'Test 2']];
        $row2 = [['text' => 'Test 3'], ['text' => 'Test 4']];

        $res = $this->message->content('*test* *1* **bold** __opa__ !<>')
            ->addReplyButtonsRow($row1)
            ->addReplyButtonsRow($row2)
            ->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    /** @test */
    public function sendMessageWithInlineKeyboard()
    {
        $row1 = [TelegramMessage::inlineButton('hello', null, 'test-1'), TelegramMessage::inlineButton('hello 2', null, 'test-1')];
        $row2 = [TelegramMessage::inlineButton('google.com', 'https://google.com')];
        $row3 = [TelegramMessage::inlineButton('callback google.com', 'https://google.com', 'test-2')];


        $res = $this->message->content('Test inline 1')
            ->addInlineButtonRow($row1)
            ->addInlineButtonRow($row2)
            ->addInlineButtonRow($row3)
            ->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    /** @test */
    public function badInlineCallback()
    {
        $this->expectException(TelegramException::class);
        TelegramMessage::inlineButton('hello', null, $this->generateRandomString(65));
    }

    /** @test */
    public function sendWebPhoto()
    {
        $res = $this->message->content('silent test')
            ->silentMode()
            ->photo('https://placeimg.com/640/480/any')
            ->send();
        $this->assertArrayHasKey('photo', $res);
    }

    /** @test */
    public function sendFileIdPhoto()
    {
        $res = $this->message->content('silent test')
            ->silentMode()
            ->photo('AgACAgQAAxkDAANzYov2-WsQ378lnaDUaNn-wk5BYiUAAhCtMRssn1RQOWhc9ljEq_oBAAMCAAN4AAMkBA')
            ->send();
        $this->assertArrayHasKey('photo', $res);
    }

    /** @test */
    public function sendFileIdVideo()
    {
        $res = $this->message->content('silent test')
            ->silentMode()
            ->video('BAACAgIAAxkBAAMYYosrKuUZunmQf8Z8hJ5T3TOxPy8AAm4XAAJcg1hI49GtY_yow5QkBA')
            ->send();
        $this->assertArrayHasKey('video', $res);
    }

    /** @test */
    public function sendMediaGroup()
    {
        $res = $this->message->content('silent test')
            ->silentMode()
            ->caption('Caption')
            ->sendMediaGroup([
                ['media' => 'BAACAgIAAxkBAAMYYosrKuUZunmQf8Z8hJ5T3TOxPy8AAm4XAAJcg1hI49GtY_yow5QkBA', 'type' => 'video'],
                ['media' => 'AgACAgQAAxkDAANzYov2-WsQ378lnaDUaNn-wk5BYiUAAhCtMRssn1RQOWhc9ljEq_oBAAMCAAN4AAMkBA', 'type' => 'photo']
            ]);
        $this->assertCount(2, $res);
        $this->assertArrayHasKey('media_group_id', $res[0]);
    }

    /** @test */
    public function sendSilent()
    {
        $res = $this->message->content('silent test')
            ->silentMode()
            ->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    /** @test */
    public function sendWithReply()
    {
        $res = $this->message->content('Reply me')
            ->silentMode()
            ->send();
        $res = $this->message->content('No problem')
            ->silentMode()
            ->replyTo($res['message_id'])
            ->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    /** @test */
    public function disableWebPagePreview()
    {
        $res = $this->message->content('Disable web page preview: https://laravel.com/docs/9.x/validation#available-validation-rules')
            ->disableWebPagePreview()
            ->send();
        $this->assertArrayHasKey('message_id', $res);
    }

    protected function setUp(): void
    {
        $env = Dotenv::createImmutable(__DIR__.'/../');
        $env->load();
        $this->message = TelegramMessage::init($_ENV['TELEGRAM_BOT_TOKEN']);
        $this->message = $this->message->sendTo($_ENV['TEST_CHAT']);

        parent::setUp(); // TODO: Change the autogenerated stub
    }
}
