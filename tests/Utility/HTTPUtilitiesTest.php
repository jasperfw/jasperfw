<?php

namespace JasperFW\JasperFWTests\Utility;

use JasperFW\JasperFW\Utility\HTTPUtilities;
use PHPUnit\Framework\TestCase;

class HTTPUtilitiesTest extends TestCase
{

    public function testGetFileExtensionFromString()
    {
        $filename = 'folder/test.php';
        $expectedExtension = 'php';
        $this->assertEquals($expectedExtension, HTTPUtilities::getFileExtension($filename));
    }

    public function testGetFileExtensionFromArray()
    {
        $filename = ['folder', 'folder/test.php'];
        $expectedExtension = 'php';
        $this->assertEquals($expectedExtension, HTTPUtilities::getFileExtension($filename));
        $filename = ['folder', 'test.php'];
        $expectedExtension = 'php';
        $this->assertEquals($expectedExtension, HTTPUtilities::getFileExtension($filename));
    }

    public function testGetFilenameFromString()
    {
        $filename = 'folder/test.php';
        $expectedName = 'test';
        $this->assertEquals($expectedName, HTTPUtilities::getFilename($filename));
    }

    public function testGetFilenameFromArray()
    {
        $filename = ['folder', 'folder/test.php'];
        $expectedName = 'test';
        $this->assertEquals($expectedName, HTTPUtilities::getFilename($filename));
        $filename = ['folder', 'test.php'];
        $expectedName = 'test';
        $this->assertEquals($expectedName, HTTPUtilities::getFilename($filename));
    }
}
