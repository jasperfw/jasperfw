<?php

namespace WigeDev\JasperCoreTests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use WigeDev\JasperCore\Core;

/**
 * Class FrameworkTestCase
 *
 * For testing, creates a mock of FW() function, which reterns a reference to a mock of the Core singleton. This class
 * can be extended easily to unit test functionality that leverages framework utilities. For example, FW()->c is a magic
 * method that returns the dependency injection container. If you use a dependency injection container in your
 * application, overload the mockGet() method to return the continer when the argument is "c".
 * @package WigeDev\JasperCoreTests
 */
class FrameworkTestCase extends TestCase
{
    /** @var Core|MockObject */
    protected $mockCore;

    protected function setUp(): void
    {
        //Get a copy of the testcase to use in the callback
        $testcase = $this;
        parent::setUp();
        //TODO: Set up mock logging
        $this->mockCore = $this->getMockBuilder(Core::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        Core::overrideFramework($this->mockCore);
        $this->mockCore->method('__get')->willReturnCallback(
            function (string $arg) use ($testcase) {
                return $testcase->mockGet($arg);
            }
        );
    }

    /**
     * Overload this function to set values to be returned by framework commands.
     *
     * @param string $argument
     *
     * @return mixed
     */
    public function mockGet(string $argument)
    {
        switch ($argument) {
            case 'log':
                return new NullLogger();
            default:
                return null;
        }
    }
}