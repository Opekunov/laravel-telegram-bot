<?php

namespace Limiter;

use Illuminate\Support\Facades\Cache;
use Opekunov\LaravelTelegramBot\Limiter\LimiterObject;
use Orchestra\Testbench\TestCase;

class LimiterObjectTest extends TestCase
{
    public function test__construct()
    {
        $ttl = 5;
        $limiter = new LimiterObject($ttl);
        $created = $limiter->updatedAt;

        $this->assertEquals($created->addSeconds($ttl), $limiter->destroyAt);
        $this->assertEquals(1, $limiter->counter);
        $this->assertEquals($created, $limiter->updatedAt);
    }

    public function testIncrement()
    {
        $limiter = new LimiterObject(5);
        $limiter->increment();

        $this->assertEquals(2, $limiter->counter);
        $this->assertTrue($limiter->updatedAt->diffInSeconds(now()) < 2);

        $limiter->increment(2);

        $this->assertEquals(4, $limiter->counter);
        $this->assertTrue($limiter->updatedAt->diffInSeconds(now()) < 2);
    }

    public function testGetTimeLeft()
    {
        $limiter = new LimiterObject(30);
        $this->assertTrue($limiter->getTimeLeft() >= 29);

        $limiter = new LimiterObject(30);
        sleep(2);
        $limiter->increment();
        $this->assertTrue($limiter->getTimeLeft() < 29);

        $limiter = new LimiterObject(1);
        $limiter->increment();
        sleep(1);
        $limiter->increment();
        $this->assertEquals(1, $limiter->counter);
        $this->assertEquals(0, $limiter->getTimeLeft());
    }

    public function testReset()
    {
        $limiter = new LimiterObject(30);
        $counter = $limiter->counter;
        $limiter->increment();
        sleep(1);
        $limiter->reset();
        $this->assertEquals($counter, $limiter->counter);
        $this->assertTrue($limiter->getTimeLeft() >= 29);
    }

    public function testCacheSet()
    {
        $limiter = new LimiterObject(1);

        Cache::put('limiter_test', $limiter, 2);
        $cachedLimiter = Cache::get('limiter_test');

        $this->assertEquals($limiter->counter, $cachedLimiter->counter);
        $this->assertEquals($limiter->updatedAt, $cachedLimiter->updatedAt);
        $this->assertEquals($limiter->destroyAt, $cachedLimiter->destroyAt);
        $this->assertEquals($limiter->getTimeLeft(), $cachedLimiter->getTimeLeft());
    }

}
