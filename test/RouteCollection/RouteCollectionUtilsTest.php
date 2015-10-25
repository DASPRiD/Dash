<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\RouteCollection;

use Dash\Exception\UnexpectedValueException;
use Dash\MatchResult\MatchResultInterface;
use Dash\MatchResult\MethodNotAllowed;
use Dash\MatchResult\SchemeNotAllowed;
use Dash\MatchResult\SuccessfulMatch;
use Dash\Route\RouteInterface;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\RouteCollection\RouteCollectionUtils;
use IteratorAggregate;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers Dash\RouteCollection\RouteCollectionUtils
 */
class RouteCollectionUtilsTest extends TestCase
{
    public function testNullReturnOnNoMatch()
    {
        $this->assertNull(RouteCollectionUtils::matchRouteCollection(
            $this->buildRouteCollection([]),
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            0,
            []
        ));
    }

    public function testSuccessfulMatchResultIsReturned()
    {
        $expectedMatchResult = $this->prophesize(SuccessfulMatch::class);
        $expectedMatchResult->getRouteName()->willReturn(null);
        $expectedMatchResult->getParams()->willReturn([]);

        $matchResult = RouteCollectionUtils::matchRouteCollection($this->buildRouteCollection([
            'foo' => null,
            'bar' => $expectedMatchResult->reveal(),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertSame('bar', $matchResult->getRouteName());
        $this->assertSame('bar', $matchResult->getRouteName());
    }

    public function testUnknownMatchResultTakesPrecedence()
    {
        $expectedMatchResult = $this->prophesize(MatchResultInterface::class)->reveal();

        $matchResult = RouteCollectionUtils::matchRouteCollection($this->buildRouteCollection([
            null,
            $this->prophesize(SchemeNotAllowed::class)->reveal(),
            $this->prophesize(MethodNotAllowed::class)->reveal(),
            $expectedMatchResult,
            'no-call',
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertSame($expectedMatchResult, $matchResult);
    }

    public function testFirstSchemeNotAllowedResultIsReturned()
    {
        $expectedMatchResult = $this->prophesize(SchemeNotAllowed::class)->reveal();

        $matchResult = RouteCollectionUtils::matchRouteCollection($this->buildRouteCollection([
            $expectedMatchResult,
            $this->prophesize(SchemeNotAllowed::class)->reveal(),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertSame($expectedMatchResult, $matchResult);
    }

    public function testSchemeNotAllowedResultTakesPrecedence()
    {
        $expectedMatchResult = $this->prophesize(SchemeNotAllowed::class)->reveal();

        $matchResult = RouteCollectionUtils::matchRouteCollection($this->buildRouteCollection([
            $expectedMatchResult,
            $this->prophesize(MethodNotAllowed::class)->reveal(),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertSame($expectedMatchResult, $matchResult);
    }

    public function testMethodNotAllowedResultsAreMerged()
    {
        $firstMatchResult = $this->prophesize(MethodNotAllowed::class);
        $firstMatchResult->getAllowedMethods()->willReturn(['GET']);

        $secondMatchResult = $this->prophesize(MethodNotAllowed::class);
        $secondMatchResult->getAllowedMethods()->willReturn(['POST']);

        $matchResult = RouteCollectionUtils::matchRouteCollection($this->buildRouteCollection([
            $firstMatchResult->reveal(),
            $secondMatchResult->reveal(),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);

        $this->assertInstanceOf(MethodNotAllowed::class, $matchResult);
        $this->assertEquals(['GET', 'POST'], $matchResult->getAllowedMethods());
    }

    public function testExceptionOnUnexpectedSuccessfulMatchResult()
    {
        $matchResultResult = $this->prophesize(MatchResultInterface::class);
        $matchResultResult->isSuccess()->willReturn(true);

        $this->setExpectedException(
            UnexpectedValueException::class,
            sprintf(
                'Expected instance of %s, received',
                SuccessfulMatch::class
            )
        );

        RouteCollectionUtils::matchRouteCollection($this->buildRouteCollection([
            $matchResultResult->reveal(),
        ]), $this->prophesize(ServerRequestInterface::class)->reveal(), 0, []);
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
