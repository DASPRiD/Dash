<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http;

use Dash\Router\Http\RouteCollection\RouteCollection;
use Dash\Router\Http\RouteMatch;
use Dash\Router\Http\Router;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Uri\Http as HttpUri;

/**
 * @covers Dash\Router\Http\Router
 */
class RouterTest extends TestCase
{
    public function testRetrieveRouteCollection()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->assertSame($routeCollection, $router->getRouteCollection());
    }

    public function testSetBasePath()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->assertNull($router->getBasePath());
        $router->setBasePath('/foo/');
        $this->assertEquals('/foo', $router->getBasePath());
    }

    public function testSetRequestUri()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->assertNull($router->getRequestUri());
        $router->setRequestUri($requestUri = $this->getMock('Zend\Uri\Http'));
        $this->assertEquals($requestUri, $router->getRequestUri());
    }

    public function testMatchDeniesInvalidRequest()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->assertNull($router->match($this->getMock('Zend\Stdlib\Request')));
    }

    public function testMatchSetsBasePath()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $router->match($this->getHttpRequest());
        $this->assertEquals('/foo', $router->getBasePath());
    }

    public function testMatchSetsRequestUri()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $router->match($this->getHttpRequest());
        $this->assertEquals('http://example.com/foo/bar', $router->getRequestUri()->toString());
    }

    public function testRouteMatchIsReturned()
    {
        $routeCollection    = new RouteCollection($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));
        $router             = new Router($routeCollection);
        $request            = $this->getHttpRequest();
        $expectedRouteMatch = new RouteMatch();

        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue($expectedRouteMatch));

        $routeCollection->insert('foo', $route);

        $routeMatch = $router->match($request);
        $this->assertSame($expectedRouteMatch, $routeMatch);
        $this->assertEquals('foo', $routeMatch->getRouteName());
    }

    public function testAssemble()
    {
        // @todo just a placeholder for the nice 100% coverage ;)
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $router->assemble();
    }

    protected function getHttpRequest()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');

        $request
            ->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('/foo'));

        $request
            ->expects($this->once())
            ->method('getUri')
            ->will($this->returnValue(new HttpUri('http://example.com/foo/bar')));

        return $request;
    }
}
