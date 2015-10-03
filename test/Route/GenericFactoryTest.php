<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\MatchResult\SuccessfulMatch;
use Dash\Parser\ParserManager;
use Dash\Route\GenericFactory;
use Dash\Route\RouteManager;
use GuzzleHttp\Psr7\Uri;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\RequestInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dash\Route\GenericFactory
 */
class GenericFactoryTest extends TestCase
{
    public function testFactoryWithoutConfiguration()
    {
        $factory = new GenericFactory();
        $route   = $factory($this->getMock(ContainerInterface::class), '');

        $this->assertNull($route->match($this->getHttpRequest(), 0));
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new GenericFactory();
        $route = $factory($this->getServiceLocator(), '', [
            '/foo',
            ['action' => 'action', 'controller' => 'controller'],
            'get',
            'hostname' => 'example.com',
            'secure' => true,
            'children' => [
                'bar' => ['path' => '/bar'],
            ],
        ]);
        $this->assertInstanceOf(SuccessfulMatch::class, $route->match($this->getHttpRequest(), 0));
    }

    public function testPathOverwritesParameter()
    {
        $factory = new GenericFactory();
        $route = $factory($this->getServiceLocator(), '', [
            '/bar',
            ['action' => 'action', 'controller' => 'controller'],
            'get',
            'hostname' => 'example.com',
            'secure' => true,
            'children' => [
                'bar' => ['path' => '/bar'],
            ],
            'path' => '/foo',
        ]);
        $this->assertInstanceOf(SuccessfulMatch::class, $route->match($this->getHttpRequest(), 0));
    }

    public function testFactoryWithSpecifiedParsers()
    {
        $factory = new GenericFactory();
        $route = $factory($this->getServiceLocator(), '', [
            '/foo/bar',
            'hostname' => 'example.com',
            'path_parser' => 'PathSegment',
            'hostname_parser' => 'HostnameSegment',
        ]);
        $this->assertInstanceOf(SuccessfulMatch::class, $route->match($this->getHttpRequest(), 0));
    }

    protected function getHttpRequest()
    {
        $request = $this->getMock(RequestInterface::class);
        $uri     = new Uri('https://example.com/foo/bar');

        $request
            ->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($uri));

        return $request;
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceLocator()
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
        ]);
    }
}
