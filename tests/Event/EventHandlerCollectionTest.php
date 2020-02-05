<?php

namespace JasperFW\JasperFWTests\Event;

use JasperFW\JasperFW\Event\EventHandler;
use JasperFW\JasperFW\Event\EventHandlerCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventHandlerCollectionTest extends TestCase
{

    public function testExecute()
    {
        /** @var EventHandler|MockObject $eh */
        $eh = $this->getMockBuilder(EventHandler::class)->disableOriginalConstructor()->onlyMethods(
            ['execute', 'getEvent']
        )->getMock();
        $eh->method('getEvent')->willReturn('testing');
        $eh->expects($this->once())->method('execute');
        $sut = new EventHandlerCollection();
        $sut->addItem($eh);
        $sut->execute('testing');
    }
}
