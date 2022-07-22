<?php

namespace Limiter;

use Illuminate\Support\Facades\Cache;
use Opekunov\LaravelTelegramBot\Limiter\RequestLimiter;
use Orchestra\Testbench\TestCase;

class RequestLimiterTest extends TestCase
{
    private RequestLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limiter = new RequestLimiter();
        Cache::flush();
    }

    public function testSetGroupsAndChannelsLimits()
    {
        $req = mt_rand();
        $time = mt_rand();
        $this->limiter->setGroupsAndChannelsLimits($req, $time);
        $this->assertEquals($req, $this->limiter->getRequestsForGroups());
        $this->assertEquals($time, $this->limiter->getLimitForGroups());
    }

    public function testSetParticularLimits()
    {
        $req = mt_rand();
        $time = mt_rand();
        $this->limiter->setParticularLimits($req, $time);
        $this->assertEquals($req, $this->limiter->getRequestsForParticular());
        $this->assertEquals($time, $this->limiter->getLimitForParticular());
    }

    public function testSetDifferenceLimits()
    {
        $req = mt_rand();
        $time = mt_rand();
        $this->limiter->setDifferenceLimits($req, $time);
        $this->assertEquals($req, $this->limiter->getRequestsForDifference());
        $this->assertEquals($time, $this->limiter->getLimitForDifference());
    }

    public function testIncreaseNotGroup()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(2, 3);
        $limiter->setDifferenceLimits(10, 10);
        $limiter->setGroupsAndChannelsLimits(10, 10);
        $limiter->increase('sendMessage', 248);
        $this->assertEquals(0, $limiter->checkLimit('sendMessage', 248));

        $limiter->increase('sendMessage', 248);
        $ttl = $limiter->checkLimit('sendMessage', 248);
        $this->assertTrue(0 < $ttl);
    }

    public function testIncreaseGroup()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(100, 100);
        $limiter->setDifferenceLimits(100, 100);
        $limiter->setGroupsAndChannelsLimits(2, 5);
        $limiter->increase('sendMessage', -248);
        $this->assertEquals(0, $limiter->checkLimit('sendMessage', -248));

        $limiter->increase('sendMessage', -248);
        $ttl = $limiter->checkLimit('sendMessage', -248);
        $this->assertTrue(0 < $ttl);
    }

    public function testIncreaseInline()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(100, 100);
        $limiter->setDifferenceLimits(100, 100);
        $limiter->setGroupsAndChannelsLimits(2, 5);
        $limiter->increase('sendMessage', null, -248);
        $this->assertEquals(0, $limiter->checkLimit('sendMessage', null, -248));

        $limiter->increase('sendMessage', null, -248);
        $ttl = $limiter->checkLimit('sendMessage', null, -248);
        $this->assertTrue(0 < $ttl);
    }

    public function testEmptyIncrease()
    {
        $limiter = $this->limiter;
        $limiter->increase('bla');
        $this->assertEquals(0, $limiter->checkLimit('bla'));
        $limiter->increase('sendMessage');
        $this->assertEquals(0, $limiter->checkLimit('sendMessage'));
    }

    public function testDropIncrease()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(2, 1);
        $limiter->setGroupsAndChannelsLimits(2, 2);
        $limiter->setDifferenceLimits(2, 3);

        $limiter->increase('sendMessage', -248);
        $ttl = $limiter->increaseAndCheck('sendMessage', -248);
        usleep($ttl / 2 * 1000);
        $this->assertTrue(0 < $limiter->checkLimit('sendMessage', -248));
        $this->assertTrue(0 < $limiter->checkLimit('sendMessage', -248));
        usleep($ttl / 2 * 1001);
        $this->assertEquals(0, $limiter->checkLimit('sendMessage', -248));
    }

    public function testMultiCheckLimit()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(1, 0);
        $limiter->setGroupsAndChannelsLimits(100, 5);
        $limiter->setDifferenceLimits(500, 100);

        for ($i = 0; $i < 101; $i++){
            $limiter->increase('sendMessage', -3000);
            $limiter->increase('sendMessage', null, 333);
        }

        $this->assertEquals(0, $limiter->checkLimit('sendMessage', 333));
        $this->assertNotEquals(0, $limiter->checkLimit('sendMessage', -333));
        $this->assertNotEquals(0, $limiter->checkLimit('sendMessage', null, 123));
    }

    public function testQueue()
    {
        $messages = range(1,300);

        $limiter = $this->limiter;
        $limiter->setDifferenceLimits(10, 1);

        $queueCounter = 0;

        foreach ($messages as $chat_id) {
            // check
            $ttl = $limiter->checkLimit('sendMessage', $chat_id);

            //if has limit -> new queue
            if($ttl > 0){
                $this->assertEquals(0, ($chat_id - 1) % 10);
                $queueCounter++;
                $limiter->setQueue($chat_id);
            }
            $limiter->increase('sendMessage', $chat_id);
        }

        $this->assertEquals(300 / 10 - 1, $queueCounter);

        $limiter->setQueue('another_queue');
        foreach ($messages as $chat_id) {
            // check
            $ttl = $limiter->checkLimit('sendMessage', $chat_id);
            if($ttl > 0) {
                $this->assertTrue(true);
                break;
            }
            $limiter->increase('sendMessage', $chat_id);
            $this->assertEquals(0, $ttl);
        }

    }

}
