<?php
namespace JasperFW\JasperCoreTests\Utility;

use JasperFW\JasperCore\Testing\FrameworkTestCase;
use JasperFW\JasperCore\Utility\Configuration;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Class ConfigurationTest
 *
 * So... most of these tests don't work as expected. This seems to be an issue with vfsStream mocking a file that is
 * include()ed. This needs some additional research before the tests for this class can be completed.
 *
 * @package JasperFW\JasperCoreTests\Utility
 */
class ConfigurationTest extends FrameworkTestCase
{
    /** @var vfsStreamDirectory The virtual file system containing the config file to test */
    protected $fileSystem;
    /** @var string The path to the config file */
    protected $url;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileSystem = vfsStream::setup('root');
        $this->url = vfsStream::url('root/config.php');
        file_put_contents($this->url, $this->generateConfigFile());
    }

    public function testGivenFolderParsesAllFilesWithin()
    {
        $this->markTestIncomplete('Future feature');
    }

    public function testGivenFileThatDoesntExistReturnFalse()
    {
        $sut = new Configuration([]);
        $this->assertFalse($sut->parseFile(vfsStream::url('config/noexists.php')));
    }

    public function testGivenFileThatCantBeReadReturnFalse()
    {
        $this->markTestIncomplete('todo');
    }

    public function testGivenFileThatIsntAnArrayReturnFalse()
    {
        $this->markTestIncomplete('todo');
    }

    public function testGivenFileThatIsValidProcessConfigurationAndReturnTrue()
    {
        $sut = new Configuration([]);
        $this->assertTrue($sut->parseFile($this->url));
    }

    public function testGivenArrayMergesWithExistingArray()
    {
        $this->markTestIncomplete('todo');
    }

    public function testGivenCategoryReturnsProperSettings()
    {
        $sut = new Configuration([]);
        $this->assertTrue($sut->parseFile(vfsStream::url('config/config.php')));
        $configuration = $sut->getConfiguration('routes');
        $expectedConfiguration = [
            'default' => [
                'route' => '/[:controller:]',
                'constraints' => [
                    'controller' => '[a-z]+',
                ],
                'defaults' => [
                    'module' => 'index',
                    'controller' => 'index',
                    'action' => 'index',
                ],
            ],
            'dashboard' => [
                'route' => '/:module:/:controller:',
                'constraints' => [
                    'module' => '[a-z]+',
                    'controller' => '[a-z]+',
                ],
                'defaults' => [
                    'module' => 'dashboard',
                    'controller' => 'index',
                    'action' => 'index',
                ],
            ],
        ];
        $this->assertEquals($expectedConfiguration, $configuration);
    }

    public function testGivenNonExistantCategoryReturnsEmptyArray()
    {
        $sut = new Configuration([]);
        $this->assertEquals([], $sut->getConfiguration('invalid'));
    }

    /**
     * Create a mock file system containing a config file
     * @return string
     */
    protected function generateConfigFile(): string
    {
        return <<<PHP
use JasperFW\JasperCore\Renderer\CLIRenderer;
use JasperFW\JasperCore\Renderer\HtmlRenderer;
use JasperFW\JasperCore\Renderer\JsonRenderer;

return array(
    'core' => array(),
    // Views control how different types of requests are displayed to the user. This lets a request for an html page, a
    // csv file and a json file all be handled by the same controller and action, and simply create the file in
    // different ways.
    'view' => array(
        'default_country' => 'us',
        'default_lang' => 'en-us',
        'default_view_type' => 'html',
        'default_layout' => 'layout/_layout.phtml',
        'renderers' => array(
            'cli' => array( // Handler for requests from the command line
                'extensions' => array('c l i'), // A special extension that can only be hit programatically
                'handler' => CLIRenderer::class
            ),
            'html' => array(
                'extensions' => array('php', 'html', 'htm'),
                'handler' => HtmlRenderer::class,
            ),
            'json' => array(
                'extensions' => array('json'),
                'handler' => JsonRenderer::class
            ),
        )
    ),
    // Routes
    'routes' => array(
        // For top level pages, this defaults to the Index module.
        'default' => array(
            'route'         => '/[:controller:]',
            'constraints'   => array(
                'controller'    => '[a-z]+'
            ),
            'defaults'      => array(
                'module'    => 'index',
                'controller'=> 'index',
                'action'    => 'index'
            )
        ),
        // More standard, folder is the module, page is the controller
        'dashboard' => array(
            'route' => '/:module:/:controller:',
            'constraints'   => array(
                'module'    => '[a-z]+',
                'controller'    => '[a-z]+'
            ),
            'defaults' => array(
                'module' => 'dashboard',
                'controller' => 'index',
                'action' => 'index'
            )
        ),
    ),
);
PHP;
    }
}
