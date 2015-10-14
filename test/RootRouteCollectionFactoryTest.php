<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\RootRouteCollectionFactory;
use Dash\Route\RouteManager;
use Dash\RouteCollection\RouteCollection;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\RootRouteCollectionFactory
 */
class RootRouteCollectionFactoryTest extends TestCase
{
    protected $config = [
        'dash' => [
            'routes' => [
                'user' => ['/user', ['action' => 'index', 'controller' => 'UserController'], 'children' => [
                    'create' => ['/create', ['action' => 'create'], ['get', 'post']],
                    'edit' => ['/edit/:id', ['action' => 'edit'], ['get', 'post'], 'constraints' => ['id' => '\d+']],
                    'delete' => ['/delete/:id', ['action' => 'delete'], 'constraints' => ['id' => '\d+']],
                ]],
            ],
            'base_uri' => 'http://example.com/'
        ],
    ];

    public function testFactorySucceedsWithoutConfig()
    {
        $factory    = new RootRouteCollectionFactory();
        $collection = $factory($this->getContainer(), '');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testFactorySucceedsWithEmptyConfig()
    {
        $factory    = new RootRouteCollectionFactory();
        $collection = $factory($this->getContainer([]), '');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testFactoryWithConfig()
    {
        $factory = new RootRouteCollectionFactory();
        $collection = $factory($this->getContainer([
            'dash' => [
                'routes' => [
                    'foo' => ['/bar'],
                ],
            ],
        ]), '');

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertAttributeSame([
            'foo' => [
                'priority' => 1,
                'serial' => 1,
                'options' => ['/bar'],
                'instance' => null,
            ],
        ], 'routes', $collection);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(array $config = null)
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RouteManager::class)->will(function () use ($container) {
            return new RouteManager($container->reveal());
        });

        if (null !== $config) {
            $container->get('config')->willReturn($config);
            $container->has('config')->willReturn(true);
        } else {
            $container->has('config')->willReturn(false);
        }

        return $container->reveal();
    }
}
