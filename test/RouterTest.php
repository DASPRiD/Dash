<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\Exception\InvalidArgumentException;
use Dash\Exception\RuntimeException;
use Dash\MatchResult\MatchResultInterface;
use Dash\MatchResult\SuccessfulMatch;
use Dash\MatchResult\UnsuccessfulMatch;
use Dash\Route\AssemblyResult;
use Dash\Route\RouteInterface;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\Router;
use IteratorAggregate;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use UnexpectedValueException;

/**
 * @covers Dash\Router
 */
class RouterTest extends TestCase
{
    public function testSetBaseUriFromObject()
    {
        $baseUri = $this->prophesize(UriInterface::class);
        $baseUri->getScheme()->willReturn('http');
        $baseUri->getHost()->willReturn('example.com');
        $baseUri->getPort()->willReturn(null);
        $baseUri->getPath()->willReturn('/foo/');

        $router = new Router($this->buildRouteCollection(), $baseUri->reveal());

        $this->assertAttributeSame([
            'scheme' => 'http',
            'host' => 'example.com',
            'port' => 80,
            'path' => '/foo',
        ], 'baseUri', $router);
    }

    public function testSetBaseUriFromString()
    {
        $router = new Router($this->buildRouteCollection(), 'http://example.com/foo/');

        $this->assertAttributeSame([
            'scheme' => 'http',
            'host' => 'example.com',
            'port' => 80,
            'path' => '/foo',
        ], 'baseUri', $router);
    }

    public function testExceptionOnMalformedBaseUriString()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Base URI "example.com:99999" does not appear to be a valid URI'
        );
        new Router($this->buildRouteCollection(), 'example.com:99999');
    }

    public function testExceptionOnInvalidBaseUri()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf(
                'Expected base URI of type string or %s, got boolean',
                UriInterface::class
            )
        );
        new Router($this->buildRouteCollection(), true);
    }

    public function testExceptionOnNonCanonicalBaseUri()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Base URI "/foo/" does not seem to be canonical'
        );
        new Router($this->buildRouteCollection(), '/foo/');
    }

    public function testPathOffsetIspPassed()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->match(Argument::type(ServerRequestInterface::class), 4)->willReturn(null);

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $router->match($this->prophesize(ServerRequestInterface::class)->reveal());
    }

    public function testUnsuccessfulMatchResultIsCreatedOnNoMatch()
    {
        $router = new Router($this->buildRouteCollection(), 'http://example.com');

        $matchResult = $router->match($this->prophesize(ServerRequestInterface::class)->reveal());
        $this->assertInstanceOf(UnsuccessfulMatch::class, $matchResult);
    }

    public function testPathOffsetIsPassed()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->match(Argument::type(ServerRequestInterface::class), 4);

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $router->match($this->prophesize(ServerRequestInterface::class)->reveal());
    }

    public function testAssemblePassesDownChildName()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->assemble([], 'bar')->willReturn(new AssemblyResult());

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $router->assemble('foo/bar');
    }

    public function testAssemblePassesNullWithoutFurtherChildren()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->assemble([], null)->willReturn(new AssemblyResult());

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $router->assemble('foo');
    }

    public function testAssembleReturnsCanonicalUriWhenForced()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->assemble([], null)->willReturn(new AssemblyResult());

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $this->assertEquals('http://example.com/foo', $router->assemble('foo', [], [
            'force_canonical' => true
        ]));
    }

    public function testAssembleQuery()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->assemble([], null)->willReturn(new AssemblyResult());

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $this->assertEquals('/foo?foo=bar', $router->assemble('foo', [], ['query' => ['foo' => 'bar']]));
    }

    public function testAssembleFragment()
    {
        $route = $this->prophesize(RouteInterface::class);
        $route->assemble([], null)->willReturn(new AssemblyResult());

        $router = new Router($this->buildRouteCollection([
            'foo' => $route->reveal(),
        ]), 'http://example.com/foo');

        $this->assertEquals('/foo#foo', $router->assemble('foo', [], ['fragment' => 'foo']));
    }

    /**
     * @param  array $routes
     * @return RouteCollectionInterface
     */
    protected function buildRouteCollection(array $routes = [])
    {
        $children = $this->prophesize(RouteCollectionInterface::class);
        $children->willImplement(IteratorAggregate::class);

        $children->getIterator()->will(function () use ($routes) {
            foreach ($routes as $key => $route) {
                yield $key => $route;
            }
        });

        $children->get(Argument::any())->will(function ($arguments) use ($routes) { return $routes[$arguments[0]]; });

        return $children->reveal();
    }
}
