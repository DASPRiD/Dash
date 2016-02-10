<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\ConfigProvider;
use Dash\Parser\ParserManager;
use Dash\Route\RouteManager;
use Dash\Router;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    public function testConfigContent()
    {
        $config = (new ConfigProvider())->__invoke();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['factories']);

        $this->assertArrayHasKey(Router::class, $config['dependencies']['factories']);
        $this->assertTrue(class_exists($config['dependencies']['factories'][Router::class]));

        $this->assertArrayHasKey(ParserManager::class, $config['dependencies']['factories']);
        $this->assertTrue(class_exists($config['dependencies']['factories'][ParserManager::class]));

        $this->assertArrayHasKey(RouteManager::class, $config['dependencies']['factories']);
        $this->assertTrue(class_exists($config['dependencies']['factories'][RouteManager::class]));

        $this->assertArrayHasKey('DashBaseUri', $config['dependencies']['factories']);
        $this->assertTrue(class_exists($config['dependencies']['factories']['DashBaseUri']));

        $this->assertArrayHasKey('DashRootRouteCollection', $config['dependencies']['factories']);
        $this->assertTrue(class_exists($config['dependencies']['factories']['DashRootRouteCollection']));
    }

    public function testConfigIsSerializable()
    {
        $config = (new ConfigProvider())->__invoke();
        $this->assertSame($config, unserialize(serialize($config)));
    }
}
