<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\Route\RouteManagerFactory;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dash\Route\RouteManagerFactory
 */
class RouteManagerFactoryTest extends TestCase
{
    public function testFactorySucceedsWithoutConfig()
    {
        $factory = new RouteManagerFactory();
        $factory($this->getServiceLocator([]), '');
    }

    public function testFactoryWithConfig()
    {
        $factory      = new RouteManagerFactory();
        $routeManager = $factory($this->getServiceLocator([
            'dash' => [
                'route_manager' => [
                    'services' => [
                        'foo' => [],
                    ],
                ],
            ],
        ]), '');

        $this->assertTrue($routeManager->has('foo'));
    }

    /**
     * @param  array $config
     * @return ServiceManager
     */
    protected function getServiceLocator(array $config)
    {
        return new ServiceManager([
            'services' => [
                'config' => $config,
            ]
        ]);
    }
}
