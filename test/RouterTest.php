<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\Exception\RuntimeException;
use Dash\Exception\UnexpectedValueException;
use Dash\MatchResult\AbstractFailedMatch;
use Dash\MatchResult\MatchResultInterface;
use Dash\MatchResult\SuccessfulMatch;
use Dash\Route\AssemblyResult;
use Dash\Route\RouteInterface;
use Dash\RouteCollection\RouteCollection;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\Router;
use GuzzleHttp\Psr7\Request as Request2;
use GuzzleHttp\Psr7\Uri;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Dash\Router
 */
class RouterTest extends TestCase
{
    public function testRetrieveRouteCollection()
    {
        $routeCollection = $this->getMock(RouteCollectionInterface::class);
        $router          = new Router($routeCollection, new Uri());

        $this->assertSame($routeCollection, $router->getRouteCollection());
    }

    public function testSuccessfulRouteMatchIsReturned()
    {
        $routeCollection    = new RouteCollection($this->getMock(ServiceLocatorInterface::class));
        $router             = new Router($routeCollection, new Uri());
        $request            = $this->getHttpRequest();
        $expectedRouteMatch = new SuccessfulMatch();

        $route = $this->getMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue($expectedRouteMatch));

        $unsuccessfulRoute = $this->getMock(RouteInterface::class);
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
        $routeCollection    = new RouteCollection($this->getMock(ServiceLocatorInterface::class));
        $router             = new Router($routeCollection, new Uri());
        $request            = $this->getHttpRequest();
        $expectedRouteMatch = $this->getMock(AbstractFailedMatch::class);

        $route = $this->getMock(RouteInterface::class);
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
        $matchResult = $this->getMock(MatchResultInterface::class);
        $matchResult
            ->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(true));

        $routeCollection    = new RouteCollection($this->getMock(ServiceLocatorInterface::class));
        $router             = new Router($routeCollection, new Uri());
        $request            = $this->getHttpRequest();

        $route = $this->getMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($request))
            ->will($this->returnValue($matchResult));

        $routeCollection->insert('foo', $route);

        $this->setExpectedException(
            UnexpectedValueException::class,
            'Expected instance of Dash\MatchResult\SuccessfulMatch, received'
        );
        $router->match($request);
    }

    public function testAssembleFailsWithoutRouteName()
    {
        $routeCollection = $this->getMock(RouteCollectionInterface::class);
        $router          = new Router($routeCollection, new Uri());

        $this->setExpectedException(RuntimeException::class, 'No route name was supplied');
        $router->assemble([], []);
    }

    public function testAssemblePassesDownChildName()
    {
        $route = $this->getMock(RouteInterface::class);
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
        $route = $this->getMock(RouteInterface::class);
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
        $route = $this->getMock(RouteInterface::class);
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
        $route  = $this->getMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo', $router->assemble([], ['name' => 'foo']));
    }

    public function testAssembleReturnsCanonicalUriWithModifications()
    {
        $route = $this->getMock(RouteInterface::class);
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
        $route = $this->getMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('http://example.com/foo', $router->assemble([], ['name' => 'foo', 'force_canonical' => true]));
    }

    public function testAssembleQuery()
    {
        $route = $this->getMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo?foo=bar', $router->assemble([], ['name' => 'foo', 'query' => ['foo' => 'bar']]));
    }

    public function testAssembleFragment()
    {
        $route  = $this->getMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue(new AssemblyResult()));
        $router = $this->getAssemblyRouter($route);

        $this->assertEquals('/foo#foo', $router->assemble([], ['name' => 'foo', 'fragment' => 'foo']));
    }

    protected function getHttpRequest()
    {
        return new Request2('GET', 'http://example.com/foo/bar');
    }

    protected function getAssemblyRouter(RouteInterface $route)
    {
        $routeCollection = $this->getMock(RouteCollectionInterface::class);
        $routeCollection
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue($route));

        return new Router($routeCollection, new Uri('http://example.com/foo'));
    }
}
