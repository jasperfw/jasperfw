<?php

namespace JasperFW\JasperFWTests\Lifecycle;

use JasperFW\JasperFW\Lifecycle\Response;
use JasperFW\JasperFW\Renderer\CLIRenderer;
use JasperFW\JasperFW\Renderer\HtmlRenderer;
use JasperFW\JasperFW\Renderer\JsonRenderer;
use JasperFW\JasperFW\Renderer\ViewHelper\MetaHelper;
use JasperFW\JasperFW\Renderer\ViewHelper\StylesheetHelper;
use JasperFW\JasperFW\Renderer\ViewHelper\TitleHelper;
use JasperFW\JasperFW\Testing\FrameworkTestCase;
use JetBrains\PhpStorm\ArrayShape;

class ResponseTest extends FrameworkTestCase
{
    public function testSetAndGet()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        /** @noinspection PhpUndefinedFieldInspection */
        $sut->testVar = 'testVal';
        $sut->setStatusCode(404);
        $this->assertEquals(404, $sut->getStatusCode());
        $sut->setViewType('csv');
        $this->assertEquals('csv', $sut->getViewType());
        $sut->setValues(['a' => 'b']);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals('testVal', $sut->testVar);
        $this->assertEquals('b', $sut->getValues()['a']);
        $sut->setData(['a' => 'b', 'c' => 'd']);
        $this->assertEquals(['a' => 'b', 'c' => 'd'], $sut->getData());
        $sut->addMessage('This is a message');
        $sut->addMessage('This is another message');
        $this->assertEquals(['This is a message', 'This is another message'], $sut->getMessages());
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertNull($sut->iWasNeverSet);
        $sut->setVariables(['geta' => 'a', 'getb' => 'b'], ['posta' => 'c', 'postb' => 'd']);
        $this->assertEquals(['geta' => 'a', 'getb' => 'b', 'posta' => 'c', 'postb' => 'd'], $sut->getVariables());
    }

    public function testMCA()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $sut->setModule('indexa');
        $sut->setController('indexa');
        $sut->setAction('indexa');
        $this->assertEquals('indexa', $sut->getModule());
        $this->assertEquals('indexa', $sut->getController());
        $this->assertEquals('indexa', $sut->getAction());
        $sut->resetMCAValues();
        $this->assertEquals('index', $sut->getModule());
        $this->assertEquals('index', $sut->getController());
        $this->assertEquals('index', $sut->getAction());
        $sut->setModule('indexb');
        $sut->setController('indexb');
        $sut->setAction('indexb');
        $this->assertEquals('indexb', $sut->getModule());
        $this->assertEquals('indexb', $sut->getController());
        $this->assertEquals('indexb', $sut->getAction());
    }

    public function testLayoutFileSpecified()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $sut->setLayoutPath('/newpath/');
        $sut->setLayoutFile('layout');
        $this->assertEquals('/newpath/', $sut->getLayoutPath());
        $this->assertEquals('layout', $sut->getLayoutFile());
    }

    public function testLayoutFileDefault()
    {
        $array = $this->getRenderersArray();
        unset($array['default_layout_path']);
        $this->confConfigWithArray($array);
        $sut = new Response();
        $this->assertEquals(_ROOT_PATH_ . DS . 'layout', $sut->getLayoutPath());
        $this->assertEquals('_default', $sut->getLayoutFile());
    }

    public function testLayoutFileFromConfig()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $this->assertEquals('layout', $sut->getLayoutPath());
        $this->assertEquals('_default', $sut->getLayoutFile());
    }

    public function testViewFileSpecified()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $sut->setViewPath('/newpath/');
        $sut->setViewFile('layout');
        $this->assertEquals('/newpath/', $sut->getViewPath());
        $this->assertEquals('layout', $sut->getViewFile());
    }

    public function testViewFileDefault()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $sut->setAction('index');
        $this->assertEquals(
            _ROOT_PATH_ . DS . 'src' . DS . 'Module' . DS . 'index' . DS . 'View' . DS . 'index',
            $sut->getViewPath()
        );
        $this->assertEquals('index', $sut->getViewFile());
    }

    public function testRendererWithValidViewType()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $sut->setViewType('json');
        $this->assertEquals('json', $sut->getViewType());
    }

    public function testRendererWithNoExtension()
    {
        $this->confConfigWithArray();
        $sut = new Response();
        $sut->setViewType('');
        $this->assertEquals('html', $sut->getViewType());
    }

    public function testRendererWithDefaultViewType()
    {
        $array = $this->getRenderersArray();
        $array['renderers']['html']['extensions'] = ['php', 'html', 'htm'];
        $this->confConfigWithArray($array);
        $sut = new Response();
        $sut->setViewType('');
        $this->assertEquals('html', $sut->getViewType());
    }

    public function testRendererNoMatchThrowsException()
    {
        $this->confConfigWithArray();
        $this->markTestSkipped('todo');
    }

    public function testRendererExceptionThrowsRenderingException()
    {
        $this->confConfigWithArray();
        $this->markTestSkipped('todo');
    }

    protected function setUp(): void
    {
        if (!defined('_ROOT_PATH_')) {
            define('_ROOT_PATH_', __DIR__);
        }
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        parent::setUp();
    }

    protected function confConfigWithArray(?array $viewArray = null): void
    {
        if (null === $viewArray) {
            $viewArray = $this->getRenderersArray();
        }
        $this->mockConfig->method('getConfiguration')->with('view')->willReturn($viewArray);
    }

    #[ArrayShape(['default_country' => "string", 'default_lang' => "string", 'defaultViewType' => "string", 'default_layout_path' => "string", 'default_layout_file' => "string", 'renderers' => "array[]"])]
    protected function getRenderersArray(): array
    {
        return [
            'default_country' => 'us',
            'default_lang' => 'en-us',
            'defaultViewType' => 'html',
            'default_layout_path' => 'layout',
            'default_layout_file' => '_default',
            'renderers' => [
                'cli' => [ // Handler for requests from the command line
                    'extensions' => ['c l i'], // A special extension that can only be hit programatically
                    'handler' => CLIRenderer::class,
                ],
                'html' => [
                    'extensions' => ['php', 'html', 'htm', '*'],
                    'handler' => HtmlRenderer::class,
                    'helpers' => [
                        'meta' => MetaHelper::class,
                        'title' => TitleHelper::class,
                        'stylesheet' => StyleSheetHelper::class,
                    ],
                ],
                'json' => [
                    'extensions' => ['json'],
                    'handler' => JsonRenderer::class,
                ],
            ],
        ];
    }
}
