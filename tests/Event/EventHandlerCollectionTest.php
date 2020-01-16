<?php

namespace WigeDev\JasperCoreTests\Event;

use PHPUnit\Framework\TestCase;
use WigeDev\JasperCore\Event\EventHandler;
use WigeDev\JasperCore\Event\EventHandlerCollection;

class EventHandlerCollectionTest extends TestCase
{

    public function testExecute()
    {
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
