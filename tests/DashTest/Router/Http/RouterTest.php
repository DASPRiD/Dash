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

    public function testSetBaseUri()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $baseUri = new HttpUri('http://example.com/foo');
        $this->assertNull($router->getBaseUri());
        $router->setBaseUri($baseUri);
        $this->assertSame($baseUri, $router->getBaseUri());
    }

    public function testMatchDeniesInvalidRequest()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->assertNull($router->match($this->getMock('Zend\Stdlib\Request')));
    }

    public function testMatchSetsBaseUri()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $router->match($this->getHttpRequest());
        $this->assertEquals('http://example.com/foo', $router->getBaseUri()->toString());
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

    public function testAssembleFailsWithoutRouteName()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->setExpectedException('Dash\Router\Exception\RuntimeException', 'No route name was supplied');
        $router->assemble([], []);
    }

    public function testAssembleFailsWithoutFindingRoute()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->setExpectedException('Dash\Router\Exception\RuntimeException', 'Route with name "foo" was not found');
        $router->assemble([], ['name' => 'foo']);
    }

    public function testAssemblePassesDownChildName()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->anything(), $this->equalTo('bar'));

        $router = $this->getAssemblyRouter($route);
        $router->assemble([], ['name' => 'foo/bar']);
    }

    public function testAssemblePassesNullWithoutFurtherChildren()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->anything(), $this->equalTo(null));

        $router = $this->getAssemblyRouter($route);
        $router->assemble([], ['name' => 'foo']);
    }

    public function testAssembleIgnoresTrailingSlash()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->anything(), $this->equalTo(null));

        $router = $this->getAssemblyRouter($route);
        $router->assemble([], ['name' => 'foo/']);
    }

    public function testAssembleReturnsRelativeUriWithoutModifications()
    {
        $route  = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo', $router->assemble([], ['name' => 'foo']));
    }

    public function testAssembleReturnsCanonicalUriWithModifications()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnCallback(function (HttpUri $uri) {
                $uri->setHost('example.org');
            }));

        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('http://example.org/foo', $router->assemble([], ['name' => 'foo']));
    }

    public function testAssembleReturnsCanonicalUriWhenForced()
    {
        $route  = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('http://example.com/foo', $router->assemble([], ['name' => 'foo', 'force_canonical' => true]));
    }

    public function testAssembleQuery()
    {
        $route  = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo?foo=bar', $router->assemble([], ['name' => 'foo', 'query' => ['foo' => 'bar']]));
    }

    public function testAssembleFragment()
    {
        $route  = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo#foo', $router->assemble([], ['name' => 'foo', 'fragment' => 'foo']));
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

    protected function getAssemblyRouter(\Dash\Router\Http\Route\RouteInterface $route)
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $routeCollection
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue($route));

        $router = new Router($routeCollection);
        $router->setBaseUri(new HttpUri('http://example.com/foo'));

        return $router;
    }
}
