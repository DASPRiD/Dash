<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http;

use Dash\Router\Http\MatchResult\SuccessfulMatch;
use Dash\Router\Http\Route\AssemblyResult;
use Dash\Router\Http\RouteCollection\RouteCollection;
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

        $matchResult = $router->match($this->getMock('Zend\Stdlib\Request'));
        $this->assertInstanceOf('Dash\Router\MatchResult\UnsupportedRequest', $matchResult);
        $this->assertEquals('Zend\Http\Request', $matchResult->getSupportedRequestClassName());
    }

    public function testMatchSetsBaseUri()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $router->match($this->getHttpRequest());
        $this->assertEquals('http://example.com/foo', $router->getBaseUri()->toString());
    }

    public function testSuccessfulRouteMatchIsReturned()
    {
        $routeCollection    = new RouteCollection($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));
        $router             = new Router($routeCollection);
        $request            = $this->getHttpRequest();
        $expectedRouteMatch = new SuccessfulMatch();

        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue($expectedRouteMatch));

        $unsuccessfulRoute = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $unsuccessfulRoute
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue(null));

        $routeCollection->insert('foo', $route);
        $routeCollection->insert('bar', $unsuccessfulRoute);

        $routeMatch = $router->match($request);
        $this->assertSame($expectedRouteMatch, $routeMatch);
        $this->assertEquals('foo', $routeMatch->getRouteName());
    }

    public function testUnsuccessfulRouteMatchIsReturned()
    {
        $routeCollection    = new RouteCollection($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));
        $router             = new Router($routeCollection);
        $request            = $this->getHttpRequest();
        $expectedRouteMatch = $this->getMock('Dash\Router\MatchResult\AbstractFailedMatch');

        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue($expectedRouteMatch));

        $routeCollection->insert('foo', $route);

        $routeMatch = $router->match($request);
        $this->assertSame($expectedRouteMatch, $routeMatch);
    }

    public function testExceptionOnUnexpectedSuccessfulMatchResult()
    {
        $matchResult = $this->getMock('Dash\Router\MatchResult\MatchResultInterface');
        $matchResult
            ->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(true));

        $routeCollection    = new RouteCollection($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));
        $router             = new Router($routeCollection);
        $request            = $this->getHttpRequest();

        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue($matchResult));

        $routeCollection->insert('foo', $route);

        $this->setExpectedException(
            'Dash\Router\Exception\UnexpectedValueException',
            'Expected instance of Dash\Router\Http\MatchResult\SuccessfulMatch, received'
        );
        $router->match($request);
    }

    public function testAssembleFailsWithoutRouteName()
    {
        $routeCollection = $this->getMock('Dash\Router\Http\RouteCollection\RouteCollectionInterface');
        $router          = new Router($routeCollection);

        $this->setExpectedException('Dash\Router\Exception\RuntimeException', 'No route name was supplied');
        $router->assemble([], []);
    }

    public function testAssemblePassesDownChildName()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->equalTo('bar'))
            ->will($this->returnValue(new AssemblyResult()));

        $router = $this->getAssemblyRouter($route);
        $router->assemble([], ['name' => 'foo/bar']);
    }

    public function testAssemblePassesNullWithoutFurtherChildren()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->equalTo(null))
            ->will($this->returnValue(new AssemblyResult()));

        $router = $this->getAssemblyRouter($route);
        $router->assemble([], ['name' => 'foo']);
    }

    public function testAssembleIgnoresTrailingSlash()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->equalTo(null))
            ->will($this->returnValue(new AssemblyResult()));

        $router = $this->getAssemblyRouter($route);
        $router->assemble([], ['name' => 'foo/']);
    }

    public function testAssembleReturnsRelativeUriWithoutModifications()
    {
        $route  = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo', $router->assemble([], ['name' => 'foo']));
    }

    public function testAssembleReturnsCanonicalUriWithModifications()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnCallback(function () {
                $assemblyResult = new AssemblyResult();
                $assemblyResult->host = 'example.org';
                return $assemblyResult;
            }));

        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('//example.org/foo', $router->assemble([], ['name' => 'foo']));
    }

    public function testAssembleReturnsCanonicalUriWhenForced()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('http://example.com/foo', $router->assemble([], ['name' => 'foo', 'force_canonical' => true]));
    }

    public function testAssembleQuery()
    {
        $route = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo?foo=bar', $router->assemble([], ['name' => 'foo', 'query' => ['foo' => 'bar']]));
    }

    public function testAssembleFragment()
    {
        $route  = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
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
