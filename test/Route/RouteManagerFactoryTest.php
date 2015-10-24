<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\Route\RouteManager;
use Dash\Route\RouteManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Route\RouteManagerFactory
 */
class RouteManagerFactoryTest extends TestCase
{
    public function testFactorySucceedsWithoutConfig()
    {
        $factory      = new RouteManagerFactory();
        $routeManager = $factory($this->prophesize(ContainerInterface::class)->reveal(), '');

        $this->assertInstanceOf(RouteManager::class, $routeManager);
    }

    public function testFactoryWithConfig()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'dash' => [
                'route_manager' => [
                    'services' => [
                        'test' => true,
                    ],
                ],
            ],
        ]);

        $factory      = new RouteManagerFactory();
        $routeManager = $factory($container->reveal(), '');

        $this->assertInstanceOf(RouteManager::class, $routeManager);
        $this->assertAttributeSame(['test' => true], 'services', $routeManager);
    }
}
