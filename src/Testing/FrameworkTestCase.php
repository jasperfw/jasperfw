<?php

namespace WigeDev\JasperCore\Testing;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use WigeDev\JasperCore\Jasper;

/**
 * Class FrameworkTestCase
 *
 * For testing, creates a mock of J() function, which reterns a reference to a mock of the Jasper singleton. This class
 * can be extended easily to unit test functionality that leverages framework utilities. For example, J()->c is a magic
 * method that returns the dependency injection container. If you use a dependency injection container in your
 * application, overload the mockGet() method to return the continer when the argument is "c".
 * @package WigeDev\JasperCore\Testing
 */
class FrameworkTestCase extends TestCase
{
    /** @var Jasper|MockObject */
    protected $mockJasper;

    protected function setUp(): void
    {
        //Get a copy of the testcase to use in the callback
        $testcase = $this;
        parent::setUp();
        //TODO: Set up mock logging
        $this->mockJasper = $this->getMockBuilder(Jasper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        Jasper::overrideFramework($this->mockJasper);
        $this->mockJasper->method('__get')->willReturnCallback(
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