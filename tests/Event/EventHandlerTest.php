<?php

namespace JasperFW\JasperCoreTests\Event;

use PHPUnit\Framework\TestCase;
use JasperFW\JasperCore\Event\EventHandler;

class EventHandlerTest extends TestCase
{
    protected $executions = 0;

    public function testGetEvent()
    {
        $sut = new EventHandler('testing', $this, 'theMethod');
        $this->assertEquals('testing', $sut->getEvent());
    }

    public function testExecute()
    {
        $sut = new EventHandler('testing', $this, 'theMethod');
        $sut->execute();
        $this->assertEquals(1, $this->executions);
    }

    public function theMethod(): void
    {
        $this->executions++;
    }
}
