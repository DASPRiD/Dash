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
use Dash\RouteCollection\RouteCollection;
use Dash\RouterInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @covers Dash\RouteCollection\RouteCollection
 */
class RouteCollectionTest extends TestCase
{
    /**
     * @var RouteInterface
     */
    protected $route;

    public function setUp()
    {
        $this->route = $this->getMock(RouterInterface::class);
    }

    public function testInsertWithRoute()
    {
        $collection = new RouteCollection($this->prophesize(RouteManager::class)->reveal(), [
            'foo' => $this->route,
        ]);

        $this->assertSame($this->route, $collection->get('foo'));
    }

    public function testInsertWithArray()
    {
        $routeManager = $this->prophesize(RouteManager::class);
        $routeManager->get('bar', ['type' => 'bar'])->willReturn($this->route);

        $collection = new RouteCollection($routeManager->reveal(), [
            'foo' => ['type' => 'bar'],
        ]);

        $this->assertSame($this->route, $collection->get('foo'));
    }

    public function testInsertWithArrayWithoutType()
    {
        $routeManager = $this->prophesize(RouteManager::class);
        $routeManager->get('Generic', [])->willReturn($this->route);

        $collection = new RouteCollection($routeManager->reveal(), [
            'foo' => [],
        ]);

        $this->assertSame($this->route, $collection->get('foo'));
    }

    public function testInsertInvalidRoute()
    {
        $this->setExpectedException(
            UnexpectedValueException::class,
            sprintf(
                'Route definition must be an array or implement %s, integer given',
                RouteInterface::class
            )
        );

        new RouteCollection($this->prophesize(RouteManager::class)->reveal(), [
            'foo' => 0,
        ]);
    }

    public function testGetNonExistentRoute()
    {
        $collection = new RouteCollection($this->prophesize(RouteManager::class)->reveal(), []);

        $this->setExpectedException(OutOfBoundsException::class, 'Route with name "foo" was not found');
        $collection->get('foo');
    }

    public function testLIFOOnly()
    {
        $collection = new RouteCollection($this->prophesize(RouteManager::class)->reveal(), [
            'foo' => $this->route,
            'bar' => $this->route,
            'baz' => $this->route,
        ]);

        $orders = [];

        foreach ($collection as $key => $value) {
            $orders[] = $key;
        }

        $this->assertSame(['baz', 'bar', 'foo'], $orders);
    }

    public function testPriorityOnly()
    {
        $routes = [
            'foo' => clone $this->route,
            'bar' => clone $this->route,
            'baz' => clone $this->route,
        ];

        $routes['foo']->priority = 1;
        $routes['bar']->priority = 0;
        $routes['baz']->priority = 2;

        $collection = new RouteCollection($this->prophesize(RouteManager::class)->reveal(), $routes);

        $orders = [];

        foreach ($collection as $key => $value) {
            $orders[] = $key;
        }

        $this->assertSame(['baz', 'foo', 'bar'], $orders);
    }

    public function testLIFOWithPriority()
    {
        $routes = [
            'foo' => clone $this->route,
            'bar' => clone $this->route,
            'baz' => clone $this->route,
        ];

        $routes['foo']->priority = 0;
        $routes['bar']->priority = 0;
        $routes['baz']->priority = 1;

        $collection = new RouteCollection($this->prophesize(RouteManager::class)->reveal(), $routes);

        $orders = [];

        foreach ($collection as $key => $value) {
            $orders[] = $key;
        }

        $this->assertSame(['baz', 'bar', 'foo'], $orders);
    }

    public function testPriorityWithNegativesAndNull()
    {
        $routes = [
            'foo' => clone $this->route,
            'bar' => clone $this->route,
            'baz' => clone $this->route,
        ];

        $routes['foo']->priority = null;
        $routes['bar']->priority = 0;
        $routes['baz']->priority = -1;

        $collection = new RouteCollection($this->prophesize(RouteManager::class)->reveal(), $routes);

        $orders = [];

        foreach ($collection as $key => $value) {
            $orders[] = $key;
        }

        $this->assertSame(['foo', 'bar', 'baz'], $orders);
    }

    public function testPriorityWithArray()
    {
        $routeManager = $this->prophesize(RouteManager::class);
        $routeManager->get('Generic', Argument::type('array'))->willReturn($this->route);

        $collection = new RouteCollection($routeManager->reveal(), [
            'foo' => ['priority' => 1],
            'bar' => ['priority' => 0],
            'baz' => ['priority' => 2],
        ]);

        $orders = [];

        foreach ($collection as $key => $value) {
            $orders[] = $key;
        }

        $this->assertSame(['baz', 'foo', 'bar'], $orders);
    }
}
