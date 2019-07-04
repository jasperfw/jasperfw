<?php
namespace WigeDev\JasperCoreTests\Utility;

use WigeDev\JasperCore\Utility\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testGivenFolderParsesAllFilesWithin()
    {
        $this->markTestIncomplete('Future development');
    }

    public function testGivenFileThatDoesntExistReturnFalse()
    {

    }

    public function testGivenFileThatCantBeReadReturnFalse()
    {

    }

    public function testGivenFileThatIsntAnArrayReturnFalse()
    {

    }

    public function testGivenFileThatIsValidProcessConfigurationAndReturnTrue()
    {

    }

    public function testGivenArrayMergesWithExistingArray()
    {

    }

    public function testGivenCategoryReturnsProperSettings()
    {

    }

    public function testGivenNonExistantCategoryReturnsEmptyArray()
    {
        $sut = new Configuration();
        $this->assertEquals([], $sut->getConfiguration('invalid'));
    }
}
