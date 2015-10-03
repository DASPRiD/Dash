<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\MatchResult\SuccessfulMatch;
use Dash\Parser\ParserManager;
use Dash\Route\RouteManager;
use Dash\RouterFactory;
use GuzzleHttp\Psr7\Request;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dash\RouterFactory
 */
class RouterFactoryTest extends TestCase
{
    protected $config = [
        'dash' => [
            'routes' => [
                'user' => ['/user', 'index', 'Application\Controller\UserController', 'children' => [
                    'create' => ['/create', 'create', 'Application\Controller\UserController', ['get', 'post']],
                    'edit' => ['/edit/:id', 'edit', 'Application\Controller\UserController', ['get', 'post'], 'constraints' => ['id' => '\d+']],
                    'delete' => ['/delete/:id', 'edit', 'Application\Controller\UserController', 'constraints' => ['id' => '\d+']],
                ]],
            ],
            'base_uri' => 'http://example.com/'
        ],
    ];

    public function testFactorySucceedsWithoutConfig()
    {
        $factory = new RouterFactory();
        $factory($this->getServiceLocator([]), '');
    }

    public function testFactoryIntegration()
    {
        $factory = new RouterFactory();
        $router  = $factory($this->getServiceLocator($this->config), '');
        $request = new Request('GET', 'http://example.com/user/edit/1');
        $match   = $router->match($request);

        $this->assertInstanceOf(SuccessfulMatch::class, $match);
        $this->assertEquals('user/edit', $match->getRouteName());
        $this->assertEquals(['controller' => 'Application\Controller\UserController', 'action' => 'edit', 'id' => '1'], $match->getParams());
    }

    /**
     * @param  array $config
     * @return ServiceManager
     */
    protected function getServiceLocator(array $config)
    {
        return new ServiceManager([
            'factories' => [
                ParserManager::class => function (ContainerInterface $container) {
                    return new ParserManager($container);
                },
                RouteManager::class => function (ContainerInterface $container) {
                    return new RouteManager($container);
                },
            ],
            'services' => [
                'config' => $config,
            ]
        ]);
    }
}
