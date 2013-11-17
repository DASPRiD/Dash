<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Mvc\Router\Http;

use Dash\Mvc\Router\Http\Parser\ParserManager;
use Dash\Mvc\Router\Http\Route\RouteManager;
use Dash\Mvc\Router\Http\RouterFactory;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dash\Mvc\Router\Http\RouterFactory
 */
class RouterFactoryTest extends TestCase
{
    protected $config = [
        'dash_router' => [
            'base_url' => '/foo',
            'routes' => [
                'user' => ['/user', 'user', 'index', 'children' => [
                    'create' => ['/create', 'user', 'create', ['get', 'post']],
                    'edit' => ['/edit/:id', 'user', 'edit', ['get', 'post'], 'constraints' => ['id' => '\d+']],
                    'delete' => ['/delete/:id', 'user', 'edit', 'constraints' => ['id' => '\d+']],
                ]],
            ],
        ],
    ];

    public function testFactoryIntegration()
    {
        $serviceLocator = $this->getServiceLocator();
        $serviceLocator->setService('config', $this->config);

        $factory = new RouterFactory();
        $router  = $factory->createService($serviceLocator);

        $this->assertEquals('/foo', $router->getBaseUrl());

        $request = new Request();
        $request->setUri('http://example.com/foo/user/edit/1');

        $match = $router->match($request);

        $this->assertInstanceOf('Dash\Mvc\Router\Http\RouteMatch', $match);
        $this->assertEquals('user/edit', $match->getRouteName());
        $this->assertEquals(['controller' => 'user', 'action' => 'edit', 'id' => '1'], $match->getParams());
    }

    public function testFactorySucceedsWithoutConfig()
    {
        $serviceLocator = $this->getServiceLocator();
        $serviceLocator->setService('config', []);

        $factory = new RouterFactory();
        $factory->createService($serviceLocator);
    }

    /**
     * @return ServiceManage;
     */
    protected function getServiceLocator()
    {
        $serviceLocator = new ServiceManager();

        $routeManager = new RouteManager();
        $routeManager->setServiceLocator($serviceLocator);
        $serviceLocator->setService('Dash\Mvc\Router\Http\Route\RouteManager', $routeManager);

        $parserManager = new ParserManager();
        $parserManager->setServiceLocator($serviceLocator);
        $serviceLocator->setService('Dash\Mvc\Router\Http\Parser\ParserManager', $parserManager);

        return $serviceLocator;
    }
}
