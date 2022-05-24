<?php

use Dotenv\Dotenv;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramException;
use Opekunov\LaravelTelegramBot\TelegramFiles;
use Opekunov\LaravelTelegramBot\TelegramMessage;
use PHPUnit\Framework\TestCase;

class TelegramFileTests extends TestCase
{
    public TelegramFiles $telegramFiles;
    public int $testUserId;

    /** @test */
    public function getFirstUserProfilePhoto()
    {
        $link = $this->telegramFiles->getFirstUserProfilePhoto($this->testUserId);
        $this->assertIsString($link);
        $this->assertTrue(!!filter_var($link, FILTER_VALIDATE_URL));
    }

    /** @test */
    public function getUserProfilePhotos()
    {
        $photos = $this->telegramFiles->getUserProfilePhotos($this->testUserId, 100, 1);
        $this->assertTrue($photos['total_count'] > 1);
    }

    protected function setUp(): void
    {
        $env = Dotenv::createImmutable(__DIR__.'/../');
        $env->load();
        $this->telegramFiles = new TelegramFiles($_ENV['TELEGRAM_BOT_TOKEN']);
        $this->testUserId = $_ENV['TEST_CHAT'];

        parent::setUp();
    }
}
