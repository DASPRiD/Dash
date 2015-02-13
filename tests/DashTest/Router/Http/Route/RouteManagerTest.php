<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\Route;

use Dash\Router\Http\Route\RouteManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\Route\RouteManager
 */
class RouteManagerTest extends TestCase
{
    public function testPassOnValidPlugin()
    {
        $routeManager = new RouteManager();
        $routeManager->validatePlugin($this->getMock('Dash\Router\Http\Route\RouteInterface'));
        // No assertions required, this will fail if an exception would occur.
    }

    public function testFailOnInvalidPlugin()
    {
        $this->setExpectedException(
            'Zend\ServiceManager\Exception\RuntimeException',
            'Plugin of type NULL is invalid; must implement Dash\Router\Http\Route\RouteInterface'
        );

        $routeManager = new RouteManager();
        $routeManager->validatePlugin(null);
    }
}
