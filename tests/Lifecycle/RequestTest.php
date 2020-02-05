<?php

namespace JasperFW\JasperFWTests\Lifecycle;

use JasperFW\JasperFW\Lifecycle\Request;
use JasperFW\JasperFW\Testing\FrameworkTestCase;

class RequestTest extends FrameworkTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $_SERVER = [
            'REQUEST_URI' => '/testbase/en-us/test/uri/file.php?a=b&c=d',
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'off',
            'SCRIPT_NAME' => __FILE__, // Keeps PHPUnit from choking
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_TIME' => time(),
            'SERVER_PORT' => 80,
        ];
        $_GET = ['a' => 'b', 'c' => 'd'];
        $_POST = ['e' => 'f'];
        $this->mockConfig->method('getConfiguration')->with('framework')->willReturn(['base' => 'testbase']);
    }

    public function testParseURI()
    {
        $sut = new Request();
        $this->assertEquals('en-us', $sut->getLocale());
        $this->assertEquals('test/uri/file', $sut->getPath());
        $this->assertEquals('php', $sut->getExtension());
        $this->assertEquals('file', $sut->getFilename());
        $this->assertEquals(['test', 'uri', 'file'], $sut->getUriPieces());
        $this->assertEquals('POST', $sut->getMethod());
        $this->assertFalse($sut->isSecure());
        $this->assertEquals(['e' => 'f'], $sut->getPost());
        $this->assertEquals(['a' => 'b', 'c' => 'd'], $sut->getQuery());
    }

    public function testGetRemoteIP()
    {
        $sut = new Request();
        $this->assertEquals('127.0.0.1', $sut->getRemoteIP());
    }

    public function testGetRemoteForwardedIP()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '::1';
        $sut = new Request();
        $this->assertEquals('::1', $sut->getRemoteIP());
        $this->assertEquals('127.0.0.1', $sut->getRawRemoteIP());
    }
}
