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
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Route\RouteManager
 */
class RouteManagerTest extends TestCase
{
    public function testRegisteredServices()
    {
        $routeManager = new RouteManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->assertTrue($routeManager->has('Generic'));
    }
}
