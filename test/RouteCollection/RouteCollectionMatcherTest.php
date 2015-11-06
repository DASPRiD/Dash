<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\RouteCollection;

use Dash\MatchResult;
use Dash\Route\RouteInterface;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\RouteCollection\RouteCollectionMatcher;
use IteratorAggregate;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @covers Dash\RouteCollection\RouteCollectionMatcher
 */
class RouteCollectionMatcherTest extends TestCase
{
    public function testNullReturnOnNoMatch()
    {
        $this->assertNull(RouteCollectionMatcher::matchRouteCollection(
            $this->buildRouteCollection([]),
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            0,
            []
        ));
    }

    public function testSuccessfulMatchResultIsReturned()
    {
        $expectedMatchResult = MatchResult::fromSuccess([]);

        $matchResult = RouteCollectionMatcher::matchRouteCollection($this->buildRouteCollection([
            'foo' => null,
            'bar' => $expectedMatchResult,
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertInstanceOf(MatchResult::class, $matchResult);
        $this->assertTrue($matchResult->isSuccess());
        $this->assertSame('bar', $matchResult->getRouteName());
    }

    public function testUnknownMatchResultTakesPrecedence()
    {
        $expectedMatchResult = MatchResult::fromMatchFailure();

        $matchResult = RouteCollectionMatcher::matchRouteCollection($this->buildRouteCollection([
            null,
            MatchResult::fromSchemeFailure($this->prophesize(UriInterface::class)->reveal()),
            MatchResult::fromMethodFailure([]),
            $expectedMatchResult,
            'no-call',
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertSame($expectedMatchResult, $matchResult);
    }

    public function testFirstSchemeNotAllowedResultIsReturned()
    {
        $expectedMatchResult = MatchResult::fromSchemeFailure($this->prophesize(UriInterface::class)->reveal());

        $matchResult = RouteCollectionMatcher::matchRouteCollection($this->buildRouteCollection([
            $expectedMatchResult,
            MatchResult::fromSchemeFailure($this->prophesize(UriInterface::class)->reveal()),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertSame($expectedMatchResult, $matchResult);
    }

    public function testSchemeNotAllowedResultTakesPrecedence()
    {
        $expectedMatchResult = MatchResult::fromSchemeFailure($this->prophesize(UriInterface::class)->reveal());

        $matchResult = RouteCollectionMatcher::matchRouteCollection($this->buildRouteCollection([
            $expectedMatchResult,
            MatchResult::fromMethodFailure([]),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertSame($expectedMatchResult, $matchResult);
    }

    public function testMethodNotAllowedResultsAreMerged()
    {
        $firstMatchResult  = MatchResult::fromMethodFailure(['GET']);
        $secondMatchResult = MatchResult::fromMethodFailure(['POST']);

        $matchResult = RouteCollectionMatcher::matchRouteCollection($this->buildRouteCollection([
            $firstMatchResult,
            $secondMatchResult,
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertInstanceOf(MatchResult::class, $matchResult);
        $this->assertTrue($matchResult->isMethodFailure());
        $this->assertEquals(['GET', 'POST'], $matchResult->getAllowedMethods());
    }

    /**
     * @param  array $routes
     * @return RouteCollectionInterface
     */
    protected function buildRouteCollection(array $routes = [])
    {
        $routeCollection = $this->prophesize(RouteCollectionInterface::class);
        $routeCollection->willImplement(IteratorAggregate::class);

        $testCase = $this;

        $routeCollection->getIterator()->will(function () use ($testCase, $routes) {
            foreach ($routes as $name => $matchResult) {
                if ('no-call' === $matchResult) {
                    $route = $testCase->prophesize(RouteInterface::class);
                    $route->match(
                        Argument::type(ServerRequestInterface::class),
                        0
                    )->shouldNotBeCalled();
                } else {
                    $route = $testCase->prophesize(RouteInterface::class);
                    $route->match(
                        Argument::type(ServerRequestInterface::class),
                        0
                    )->shouldBeCalled()->willReturn($matchResult);
                }

                yield $name => $route->reveal();
            }
        });

        return $routeCollection->reveal();
    }
}
