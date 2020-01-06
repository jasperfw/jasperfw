<?php
namespace WigeDev\JasperCoreTests\Utility;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use WigeDev\JasperCore\Utility\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @var vfsStreamDirectory The virtual file system containing the config file to test
     */
    protected $fileSystem;
    /**
     * @var string The path to the config file
     */
    protected $url;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileSystem = vfsStream::setup('root', null, $this->generateConfigDirectory());
        $this->url = vfsStream::url('config/config.php');
    }

    public function testGivenFolderParsesAllFilesWithin()
    {
        $this->markTestIncomplete('Future feature');
    }

    public function testGivenFileThatDoesntExistReturnFalse()
    {
        $sut = new Configuration();
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
        $sut = new Configuration();
        $this->assertTrue($sut->parseFile(vfsStream::url('config/config.php')));
    }

    public function testGivenArrayMergesWithExistingArray()
    {
        $this->markTestIncomplete('todo');
    }

    public function testGivenCategoryReturnsProperSettings()
    {
        $sut = new Configuration();
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
        $sut = new Configuration();
        $this->assertEquals([], $sut->getConfiguration('invalid'));
    }

    /**
     * Create a mock file system containing a config file
     * @return array
     */
    protected function generateConfigDirectory(): array
    {
        return [
            'config' => [
                'config.php' => <<<PHP
use WigeDev\JasperCore\Renderer\CLIRenderer;
use WigeDev\JasperCore\Renderer\HtmlRenderer;
use WigeDev\JasperCore\Renderer\JsonRenderer;
use WigeDev\JasperCore\Renderer\ViewHelper\MetaHelper;
use WigeDev\JasperCore\Renderer\ViewHelper\StylesheetHelper;
use WigeDev\JasperCore\Renderer\ViewHelper\TitleHelper;
use WigeDev\JasperCore\ServiceManager\ViewManager;

return array(
    'core' => array(),
    'service_managers' => array(
        'view' => new ViewManager(),
    ),
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
                'helpers' => array(
                    'meta' => new MetaHelper(),
                    'title' => new TitleHelper(),
                    'stylesheet' => new StyleSheetHelper('meta'),
                )
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
PHP
                ,

            ],
        ];
    }
}
