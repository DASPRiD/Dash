<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\RouteCollection\RouteCollectionInterface;
use Dash\Router;
use Dash\RouterFactory;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\RouterFactory
 */
class RouterFactoryTest extends TestCase
{
    public function testBaseUriIsPassed()
    {
        $this->assertAttributeSame(
            [
                'scheme' => 'http',
                'host' => 'example.com',
                'port' => 80,
                'path' => '',
            ],
            'baseUri',
            $this->getRouter()
        );
    }

    public function testRootRouteCollectionIsPassed()
    {
        $this->assertTrue(self::readAttribute($this->getRouter(), 'routeCollection')->get('test'));
    }

    /**
     * @return Router
     */
    protected function getRouter()
    {
        $routeCollection = $this->prophesize(RouteCollectionInterface::class);
        $routeCollection->get('test')->willReturn(true);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('DashRootRouteCollection')->willReturn($routeCollection->reveal());
        $container->get('DashBaseUri')->willReturn('http://example.com');

        $factory = new RouterFactory();
        $router  = $factory($container->reveal(), '');

        $this->assertInstanceOf(Router::class, $router);

        return $router;
    }
}
