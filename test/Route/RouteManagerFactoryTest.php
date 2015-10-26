<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\AbstractPluginManagerFactory;
use Dash\Route\RouteManager;
use Dash\Route\RouteManagerFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Route\RouteManagerFactory
 */
class RouteManagerFactoryTest extends TestCase
{
    public function testFactoryUsesAbstractPluginManagerFactory()
    {
        $factory = new RouteManagerFactory();
        $this->assertInstanceOf(AbstractPluginManagerFactory::class, $factory);
    }

    public function testFactorySettings()
    {
        $factory = new RouteManagerFactory();

        $invoker = function ($methodName) { return $this->{$methodName}(); };
        $protectedInvoker = $invoker->bindTo($factory, $factory);

        $this->assertSame('route_manager', $protectedInvoker('getConfigKey'));
        $this->assertSame(RouteManager::class, $protectedInvoker('getClassName'));
    }
}
