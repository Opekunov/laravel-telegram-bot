<?php

namespace Limiter;

use Illuminate\Support\Facades\Cache;
use Opekunov\LaravelTelegramBot\Limiter\LimiterObject;
use Opekunov\LaravelTelegramBot\Limiter\LimitersGroup;
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

    public function testGetLimiters()
    {
        $limiters = $this->limiter->getLimiters();
        $this->assertInstanceOf(LimitersGroup::class, $limiters);
        $this->assertInstanceOf(LimiterObject::class, $limiters->groups);
        $this->assertInstanceOf(LimiterObject::class, $limiters->difference);
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
        $limiter->setParticularLimits(1, 3);
        $baseLimiters = $limiter->getLimiters();
        $baseParticularLimiter = $limiter->getParticularLimiter(248);

        $limiter->increase('sendMessage', 248);
        $limiters = $limiter->getLimiters();
        $particularLimiter = $limiter->getParticularLimiter(248);

        $this->assertTrue($limiters->difference->counter > $baseLimiters->difference->counter);
        $this->assertTrue($limiters->groups->counter === $baseLimiters->groups->counter);
        $this->assertTrue($particularLimiter->counter > $baseParticularLimiter->counter);
    }

    public function testIncreaseGroup()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(1, 3);
        $baseLimiters = $limiter->getLimiters();
        $baseParticularLimiter = $limiter->getParticularLimiter(248);

        $limiter->setParticularLimits(1, 3);
        $limiter->increase('sendMessage', -248);
        $limiters = $limiter->getLimiters();
        $particularLimiter = $limiter->getParticularLimiter(-248);

        $this->assertTrue($limiters->difference->counter > $baseLimiters->difference->counter);
        $this->assertTrue($limiters->groups->counter > $baseLimiters->groups->counter);
        $this->assertTrue($particularLimiter->counter > $baseParticularLimiter->counter);
    }

    public function testIncreaseInline()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(1, 3);
        $baseLimiters = $limiter->getLimiters();
        $baseParticularLimiter = $limiter->getParticularLimiter();

        $limiter->increase('sendMessage', null, -248);
        $limiters = $limiter->getLimiters();
        $particularLimiter = $limiter->getParticularLimiter();

        $this->assertTrue($limiters->difference->counter > $baseLimiters->difference->counter);
        $this->assertTrue($limiters->groups->counter > $baseLimiters->groups->counter);
        $this->assertTrue($particularLimiter->counter === $baseParticularLimiter->counter);
    }

    public function testEmptyIncrease()
    {
        $limiter = $this->limiter;
        $limiter->increase('bla');
        $this->assertTrue($limiter->getLimiters()->difference->counter === 0);
        $limiter->increase('sendMessage');
        $this->assertTrue($limiter->getLimiters()->difference->counter === 0);
    }

    public function testDoubleIncrease()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(1, 3);
        $baseLimiters = $limiter->getLimiters();
        $baseParticularLimiter = $limiter->getParticularLimiter();

        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $limiters = $limiter->getLimiters();
        $particularLimiter = $limiter->getParticularLimiter(-248);

        $this->assertTrue($limiters->difference->counter === $baseLimiters->difference->counter + 2);
        $this->assertTrue($limiters->groups->counter === $baseLimiters->groups->counter + 2);
        $this->assertTrue($particularLimiter->counter === $baseParticularLimiter->counter + 2);
    }

    public function testDropIncrease()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(3, 2);
        $limiter->setGroupsAndChannelsLimits(3, 2);
        $limiter->setDifferenceLimits(3, 2);

        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        sleep(3);
        $limiters = $limiter->getLimiters();
        $particularLimiter = $limiter->getParticularLimiter(-248);

        $this->assertTrue($limiters->difference->counter === 0);
        $this->assertTrue($limiters->groups->counter === 0);
        $this->assertTrue($particularLimiter->counter === 0);
    }

    public function testCheckLimit()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(3, 2);
        $limiter->setGroupsAndChannelsLimits(3, 2);
        $limiter->setDifferenceLimits(3, 2);

        $this->assertTrue($limiter->checkLimit('sendMessage', -248));

        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $this->assertFalse($limiter->checkLimit('sendMessage', -248));

        sleep(3);
        $this->assertTrue($limiter->checkLimit('sendMessage', -248));
    }

    public function testAnotherCheckLimit()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(1, 5);
        $limiter->setGroupsAndChannelsLimits(100, 5);
        $limiter->setDifferenceLimits(100, 100);

        $this->assertTrue($limiter->checkLimit('sendMessage', -248));

        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -248);
        $limiter->increase('sendMessage', -2438);
        sleep(3);
        $this->assertFalse($limiter->checkLimit('sendMessage', -248));
        $this->assertTrue($limiter->checkLimit('sendMessage', -2438));
        sleep(3);
        $this->assertTrue($limiter->checkLimit('sendMessage', -248));
    }

    public function testMultiCheckLimit()
    {
        $limiter = $this->limiter;
        $limiter->setParticularLimits(1, 0);
        $limiter->setGroupsAndChannelsLimits(100, 5);
        $limiter->setDifferenceLimits(200, 100);

        for ($i = 0; $i < 101; $i++){
            $limiter->increase('sendMessage', -3000);
        }
       /* $this->assertFalse($limiter->checkLimit('sendMessage', -3000));
        $this->assertFalse($limiter->checkLimit('sendMessage', -200));
        $this->assertFalse($limiter->checkLimit('sendMessage', null, 123));*/

        var_dump($limiter->getParticularLimiter(333)->counter);
        $this->assertTrue($limiter->checkLimit('sendMessage', 333));
    }

    public function testGetParticularLimiter()
    {
        $this->assertInstanceOf(LimiterObject::class, $this->limiter->getParticularLimiter());
    }

    public function testSaveLimiters()
    {
        $res = $this->limiter->saveLimiters($this->limiter->getLimiters(), $this->limiter->getParticularLimiter(23), 23);
        $this->assertTrue($res);

        $res = $this->limiter->saveLimiters($this->limiter->getLimiters(), $this->limiter->getParticularLimiter());
        $this->assertTrue($res);
    }
}
