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
        $serviceManager = new ServiceManager(
            $config['service_manager'] + ['services' => ['config' => []]]
        );

        $this->assertInstanceOf(
            ParserManager::class,
            $serviceManager->get(ParserManager::class)
        );

        $this->assertInstanceOf(
            RouteManager::class,
            $serviceManager->get(RouteManager::class)
        );

        $this->assertInstanceOf(
            Router::class,
            $serviceManager->get(Router::class)
        );
    }
}
