<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\RouteCollection;

use Dash\Exception\OutOfBoundsException;
use Dash\Exception\UnexpectedValueException;
use Dash\Route\RouteInterface;
use Dash\Route\RouteManager;
use Dash\RouteCollection\LazyRouteCollection;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;

/**
 * @covers Dash\RouteCollection\LazyRouteCollection
 */
class LazyRouteCollectionTest extends TestCase
{
    /**
     * @var RouteInterface
     */
    protected $route;

    public function setUp()
    {
        $this->route = $this->prophesize(RouteInterface::class)->reveal();
    }

    public function sortProvider()
    {
        return [
            'lifo-only' => [
                ['foo' => [], 'bar' => [], 'baz' => []],
                ['baz', 'bar', 'foo'],
            ],
            'priority-only' => [
                ['foo' => ['priority' => 1], 'bar' => ['priority' => 0], 'baz' => ['priority' => 2]],
                ['baz', 'foo', 'bar'],
            ],
            'lifo-with-priority' => [
                ['foo' => ['priority' => 0], 'bar' => ['priority' => 0], 'baz' => ['priority' => 1]],
                ['baz', 'bar', 'foo'],
            ],
            'priority-with-negative-and-null-values' => [
                ['foo' => ['priority' => null], 'bar' => ['priority' => 0], 'baz' => ['priority' => -1]],
                ['foo', 'bar', 'baz'],
            ],
        ];
    }

    public function testInsertWithArray()
    {
        $routeManager = $this->prophesize(RouteManager::class);
        $routeManager->get('bar', ['type' => 'bar'])->willReturn($this->route);

        $collection = new LazyRouteCollection($routeManager->reveal(), [
            'foo' => ['type' => 'bar'],
        ]);

        $this->assertSame($this->route, $collection->get('foo'));
    }

    public function testInsertWithArrayWithoutType()
    {
        $routeManager = $this->prophesize(RouteManager::class);
        $routeManager->get('Generic', [])->willReturn($this->route);

        $collection = new LazyRouteCollection($routeManager->reveal(), [
            'foo' => [],
        ]);

        $this->assertSame($this->route, $collection->get('foo'));
    }

    public function testInsertInvalidRoute()
    {
        $this->setExpectedException(
            UnexpectedValueException::class,
            'Route definition must be an array, integer given'
        );

        new LazyRouteCollection($this->prophesize(RouteManager::class)->reveal(), [
            'foo' => 0,
        ]);
    }

    public function testGetNonExistentRoute()
    {
        $collection = new LazyRouteCollection($this->prophesize(RouteManager::class)->reveal(), []);

        $this->setExpectedException(OutOfBoundsException::class, 'Route with name "foo" was not found');
        $collection->get('foo');
    }

    /**
     * @dataProvider sortProvider
     * @param array $routes
     * @param array $expectedOrder
     */
    public function testSort(array $routes, array $expectedOrder)
    {
        $routeManager = $this->prophesize(RouteManager::class);
        $routeManager->get('Generic', Argument::type('array'))->willReturn($this->route);

        $collection = new LazyRouteCollection($routeManager->reveal(), $routes);

        $orders = [];

        foreach ($collection as $key => $value) {
            $orders[] = $key;
        }

        $this->assertSame($expectedOrder, $orders);
    }
}
