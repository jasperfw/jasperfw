<?php

namespace WigeDev\JasperCoreTests\Lifecycle;

use PHPUnit\Framework\TestCase;
use WigeDev\JasperCore\Lifecycle\Request;

class RequestTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $_SERVER = [
            'REQUEST_URI' => 'base/en-us/test/uri/file.php?a=b&c=d',
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'off',
        ];
        $_GET = ['a' => 'b', 'c' => 'd'];
        $_POST = ['e' => 'f'];
    }

    public function testParseURI()
    {
        $sut = new Request();
        $this->assertEquals('en-us', $sut->getLocale());
        $this->assertEquals('test/uri/file.php', $sut->getPath());
        $this->assertEquals('php', $sut->getExtension());
        $this->assertEquals('file', $sut->getFilename());
        $this->assertEquals(['test', 'uri', 'file.php'], $sut->getUriPieces());
        $this->assertEquals('POST', $sut->getMethod());
    }

    public function testGetRemoteIP()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $sut = new Request();
        $this->assertEquals('127.0.0.1', $sut->getRemoteIP());
    }

    public function testGetRemoteForwardedIP()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '::1';
        $sut = new Request();
        $this->assertEquals('::1', $sut->getRemoteIP());
        $this->assertEquals('127.0.0.1', $sut->getRawRemoteIP());
    }
}
