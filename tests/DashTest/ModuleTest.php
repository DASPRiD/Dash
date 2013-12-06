<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\Module;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Loader\AutoloaderFactory;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dash\Module
 */
class ModuleTest extends TestCase
{
    /**
     * @var Module
     */
    protected $module;

    public function setUp()
    {
        $this->module = new Module();
    }

    public function testServiceSetup()
    {
        $config = $this->module->getConfig();
        $serviceManager = new ServiceManager(new Config($config['service_manager']));
        $serviceManager->setService('Config', []);

        $this->assertInstanceOf(
            'Dash\Router\Http\Parser\ParserManager',
            $serviceManager->get('Dash\Router\Http\Parser\ParserManager')
        );

        $this->assertInstanceOf(
            'Dash\Router\Http\Route\RouteManager',
            $serviceManager->get('Dash\Router\Http\Route\RouteManager')
        );

        $this->assertInstanceOf(
            'Dash\Router\Http\Router',
            $serviceManager->get('Dash\Router\Http\Router')
        );
    }

    public function testAutoloaderSetup()
    {
        $config = $this->module->getAutoloaderConfig();
        AutoloaderFactory::factory($config);

        $autoloaders = AutoloaderFactory::getRegisteredAutoloaders();
        $this->assertEquals(1, count($autoloaders));
        $this->assertTrue(isset($autoloaders['Zend\Loader\StandardAutoloader']));

        $autoloader = $autoloaders['Zend\Loader\StandardAutoloader'];
        $this->assertInstanceOf('Zend\Loader\StandardAutoloader', $autoloader);

        AutoloaderFactory::unregisterAutoloaders();
    }
}
