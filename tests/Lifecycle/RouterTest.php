<?php

namespace JasperFW\JasperFWTests\Lifecycle;

use Exception;
use JasperFW\JasperFW\Lifecycle\Request;
use JasperFW\JasperFW\Lifecycle\Response;
use JasperFW\JasperFW\Lifecycle\Router;
use JasperFW\JasperFW\Testing\FrameworkTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RouterTest extends FrameworkTestCase
{
    /** @var MockObject|Request */
    protected Request|MockObject $request;
    /** @var MockObject|Response */
    protected MockObject|Response $response;

    public function setUp(): void
    {
        parent::setUp();
        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getURI', 'getUriPieces', 'getExtension'])
            ->getMock();
        $this->response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'resetMCAValues',
                    'setModule',
                    'setController',
                    'setAction',
                    'setViewType',
                    'addMessage',
                    'setStatusCode',
                ]
            )
            ->getMock();
    }

    /**
     * @throws Exception
     */
    public function testRoute()
    {
        $this->request->method('getURI')->willReturn('/something/somethingelse');
        $this->request->method('getUriPieces')->willReturn(['something', 'somethingelse']);
        $this->request->method('getExtension')->willReturn('html');
        $this->response->expects($this->atLeastOnce())->method('resetMCAValues');
        $this->response->expects($this->once())->method('setModule')->with($this->equalTo('something'));
        $this->response->expects($this->once())->method('setController')->with($this->equalTo('somethingelse'));
        $this->response->expects($this->once())->method('setAction')->with($this->equalTo('index'));
        $this->response->expects($this->once())->method('setViewType')->with(
            $this->equalTo('c l i')
        ); // Because PHPUnit is cli
        $sut = new Router();
        $sut->route($this->request, $this->response);
    }

    /**
     * @throws Exception
     */
    public function testRouteDoesNotMatchRoute()
    {
        $this->request->method('getURI')->willReturn('/something/somethingelse/nope/notgood/bad');
        $this->request->method('getUriPieces')->willReturn(['something', 'somethingelse', 'nope', 'notgood', 'bad']);
        $this->request->method('getExtension')->willReturn('html');
        $this->response->expects($this->atLeastOnce())->method('resetMCAValues');
        $this->response->expects($this->once())->method('setStatusCode')->with($this->equalTo(404));
        $this->response->expects($this->once())->method('addMessage')->with(
            $this->equalTo(
                'The requested URL /something/somethingelse/nope/notgood/bad could not be found.'
            )
        );
        $sut = new Router();
        $sut->route($this->request, $this->response);
    }

    protected function configureConfiguration(): void
    {
        parent::configureConfiguration();
        $this->mockConfig->method('getConfiguration')->with('routes')->willReturn(
            [
                'default' => [
                    'route' => '/[:module:]',
                    'constraints' => [
                        'module' => '[a-z]+',
                    ],
                    'defaults' => [
                        'module' => 'index',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
                // More standard, folder is the module, page is the controller, subpage can be the action.
                'mvc' => [
                    'route' => '/:module:/:controller:[/:action:]',
                    'constraints' => [
                        'module' => '[a-z]+',
                        'controller' => '[a-z]+',
                        'action' => '[a-z]+',
                    ],
                    'defaults' => [
                        'module' => 'dashboard',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
            ]
        );
    }
}
