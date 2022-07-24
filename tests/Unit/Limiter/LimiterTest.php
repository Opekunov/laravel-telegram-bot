<?php
/*
 * Copyright (c)
 * Opekunov Aleksandr <iam@opekunov.com>
 */

namespace Unit\Limiter;

use Illuminate\Support\Facades\Cache;
use Opekunov\LaravelTelegramBot\Limiter\Limiter;
use Orchestra\Testbench\TestCase;

class LimiterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testSetUpdatedAt()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setUpdatedAt(1000);
        $this->assertEquals(1000, $limiter->getUpdatedAt());
    }

    public function testIsLess()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->increment();
        $this->assertTrue($limiter->isLess());
        $this->assertTrue($limiter->isLess(9));
        $limiter->increment(10);
        $this->assertFalse($limiter->isLess());
    }

    public function testTouch()
    {
        $limiter = new Limiter('l', 10, 2);
        $limiter->increment();
        $this->assertEquals(1, $limiter->getCounter());
        usleep($limiter->getTimeLeft() * 1000);
        $limiter->touch();
        $this->assertEquals(0, $limiter->getCounter());
    }

    public function testIsLessOrEqual()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->increment();
        $this->assertTrue($limiter->isLessOrEqual());
        $this->assertTrue($limiter->isLessOrEqual(10));
        $this->assertFalse($limiter->isLessOrEqual(0));
        $limiter->increment(10);
        $this->assertFalse($limiter->isLessOrEqual());
    }

    public function testSetDestroyAt()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setDestroyAt(1000);
        $this->assertEquals(1000, $limiter->getDestroyAt());
    }

    public function testSetCreatedAt()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setCreatedAt(1000);
        $this->assertEquals(1000, $limiter->getCreatedAt());
    }

    public function testGetOrCreate()
    {
        $limiter = Limiter::getOrCreate('l_test_2', 10, 300);
        $this->assertInstanceOf(Limiter::class, $limiter);
        $this->assertTrue(Cache::has('l_test_2'));

        $limiter->increment();
        $limiter = Limiter::getOrCreate('l_test_2', 10, 300);
        $this->assertEquals(1, $limiter->getCounter());
    }

    public function testIncrement()
    {
        $limiter = Limiter::getOrCreate('l_test_2', 10, 2);
        $limiter->increment();
        $this->assertEquals(1, $limiter->getCounter());
        $limiter->increment(10);
        $this->assertEquals(11, $limiter->getCounter());
        usleep($limiter->getTimeLeft() * 1000);
        $this->assertEquals(0, $limiter->getCounter());
    }

    public function testReset()
    {
        $limiter = Limiter::getOrCreate('l_test_2', 10, 2);
        $limiter->increment(10);
        $limiter->reset();
        $this->assertEquals(0, $limiter->getCounter());
    }

    public function testIncrementIf()
    {
        $limiter = Limiter::getOrCreate('l_test_2', 10, 2);
        $limiter->incrementIf(fn() => 2 > 1);
        $this->assertEquals(1, $limiter->getCounter());
        $limiter->incrementIf(fn() => 1 > 2);
        $this->assertEquals(1, $limiter->getCounter());
    }

    public function testToArray()
    {
        $limiter = Limiter::getOrCreate('l_test_2', 10, 2);
        $this->assertIsArray($limiter->toArray());
        $this->assertArrayHasKey('max', $limiter->toArray());
        $this->assertArrayHasKey('createdAt', $limiter->toArray());
        $this->assertArrayHasKey('updatedAt', $limiter->toArray());
        $this->assertArrayHasKey('destroyAt', $limiter->toArray());
        $this->assertArrayHasKey('counter', $limiter->toArray());
        $this->assertArrayHasKey('ttl', $limiter->toArray());
        $this->assertArrayHasKey('key', $limiter->toArray());
    }

    public function testSetTtl()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setTtl(1000);
        $this->assertEquals(1000, $limiter->getTtl());
    }

    public function testSetMax()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setMax(1000);
        $this->assertEquals(1000, $limiter->getMax());
    }

    public function testSave()
    {
        $limiter = new Limiter('l_test_22', 10, 300);
        $limiter->save();
        $this->assertTrue(Cache::has('l_test_22'));
    }

    public function testIsMore()
    {
        $limiter = new Limiter('l', 0, 300);
        $limiter->increment(2);
        $this->assertTrue($limiter->isMore());
        $this->assertTrue($limiter->isMore(0));
        $limiter->increment(10);
        $this->assertTrue($limiter->isMore());
        $this->assertFalse($limiter->isMore(10000));
    }

    public function testSetKey()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setKey('key');
        $this->assertEquals('key', $limiter->getKey());
    }

    public function testIsMoreOrEqual()
    {
        $limiter = new Limiter('l', 2, 300);
        $limiter->increment(2);
        $this->assertTrue($limiter->isMoreOrEqual());
        $this->assertTrue($limiter->isMoreOrEqual(1));
        $limiter->increment(10);
        $this->assertTrue($limiter->isMoreOrEqual());
        $this->assertFalse($limiter->isMoreOrEqual(10000));
    }

    public function testSetCounter()
    {
        $limiter = new Limiter('l', 10, 300);
        $limiter->setCounter(1000);
        $this->assertEquals(1000, $limiter->getCounter());
    }
}
