<?php

namespace JasperFW\JasperFWTests\Event;

use JasperFW\JasperFW\Event\EventHandler;
use PHPUnit\Framework\TestCase;

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
