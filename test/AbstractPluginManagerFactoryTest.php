<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\AbstractPluginManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionProperty;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * @covers Dash\AbstractPluginManagerFactory
 */
class AbstractPluginManagerFactoryTest extends TestCase
{
    public function testFactorySucceedsWithoutConfig()
    {
        $factory       = $this->buildFactory();
        $parserManager = $factory($this->prophesize(ContainerInterface::class)->reveal(), '');

        $this->assertInstanceOf(AbstractPluginManager::class, $parserManager);
    }

    public function testFactoryWithConfig()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'dash' => [
                'manager' => [
                    'services' => [
                        'test' => true,
                    ],
                ],
            ],
        ]);

        $factory       = $this->buildFactory();
        $parserManager = $factory($container->reveal(), '');

        $this->assertInstanceOf(AbstractPluginManager::class, $parserManager);
        $this->assertAttributeSame(['test' => true], 'services', $parserManager);
    }

    protected function buildFactory()
    {
        $pluginManager = $this->prophesize()->willExtend(AbstractPluginManager::class)->reveal();
        $factory = $this->prophesize()->willExtend(AbstractPluginManagerFactory::class)->reveal();

        $reflectionProperty = new ReflectionProperty(AbstractPluginManagerFactory::class, 'configKey');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($factory, 'manager');

        $reflectionProperty = new ReflectionProperty(AbstractPluginManagerFactory::class, 'className');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($factory, get_class($pluginManager));

        return $factory;
    }
}
