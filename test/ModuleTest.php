<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\Module;
use Dash\Parser\ParserManager;
use Dash\Route\RouteManager;
use Dash\Router;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * @covers Dash\Module
 */
class ModuleTest extends TestCase
{
    public function testGetConfig()
    {
        $config = (new Module())->getConfig();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertInternalType('array', $config['service_manager']);
        $this->assertArrayHasKey('factories', $config['service_manager']);
        $this->assertInternalType('array', $config['service_manager']['factories']);

        $this->assertArrayHasKey(Router::class, $config['service_manager']['factories']);
        $this->assertImplements(FactoryInterface::class, $config['service_manager']['factories'][Router::class]);

        $this->assertArrayHasKey(ParserManager::class, $config['service_manager']['factories']);
        $this->assertImplements(FactoryInterface::class, $config['service_manager']['factories'][ParserManager::class]);

        $this->assertArrayHasKey(RouteManager::class, $config['service_manager']['factories']);
        $this->assertImplements(FactoryInterface::class, $config['service_manager']['factories'][RouteManager::class]);

        $this->assertSame($config, unserialize(serialize($config)));
    }

    protected function assertImplements($expected, $actual)
    {
        $this->assertTrue(
            is_subclass_of($actual, $expected),
            sprintf('%s does not implement %s', $actual, $expected)
        );
    }
}
