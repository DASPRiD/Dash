<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\Parser\ParserInterface;
use Dash\Parser\ParserManager;
use Dash\Route\Generic;
use Dash\Route\GenericFactory;
use Dash\Route\RouteManager;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;

/**
 * @covers Dash\Route\GenericFactory
 */
class GenericFactoryTest extends TestCase
{
    public function testFactoryWithoutConfiguration()
    {
        $factory = new GenericFactory();
        $route   = $factory($this->prophesize(ContainerInterface::class)->reveal(), '');

        $this->assertInstanceOf(Generic::class, $route);
        $this->assertAttributeSame(null, 'pathParser', $route);
        $this->assertAttributeSame([], 'defaults', $route);
        $this->assertAttributeSame(null, 'methods', $route);
        $this->assertAttributeSame(null, 'hostnameParser', $route);
        $this->assertAttributeSame(null, 'port', $route);
        $this->assertAttributeSame(null, 'secure', $route);
        $this->assertAttributeSame(null, 'children', $route);
    }

    public function testFactoryWithEmptyConfiguration()
    {
        $factory = new GenericFactory();
        $route   = $factory($this->prophesize(ContainerInterface::class)->reveal(), '', []);

        $this->assertInstanceOf(Generic::class, $route);
        $this->assertAttributeSame(null, 'pathParser', $route);
        $this->assertAttributeSame([], 'defaults', $route);
        $this->assertAttributeSame(null, 'methods', $route);
        $this->assertAttributeSame(null, 'hostnameParser', $route);
        $this->assertAttributeSame(null, 'port', $route);
        $this->assertAttributeSame(null, 'secure', $route);
        $this->assertAttributeSame(null, 'children', $route);
    }

    public function testFactoryWithFullConfiguration()
    {
        $factory = new GenericFactory();
        $route = $factory($this->getContainer('/foo', 'example.com'), '', [
            '/foo',
            ['foo' => 'bar'],
            ['POST'],
            'hostname' => 'example.com',
            'port' => 88,
            'secure' => true,
            'children' => [
                'bar' => ['path' => '/bar'],
            ],
        ]);

        $this->assertInstanceOf(Generic::class, $route);
        $this->assertAttributeSame(['foo' => 'bar'], 'defaults', $route);
        $this->assertAttributeSame(['POST'], 'methods', $route);
        $this->assertAttributeSame(88, 'port', $route);
        $this->assertAttributeSame(true, 'secure', $route);
        $this->assertArrayHasKey('bar', self::readAttribute(self::readAttribute($route, 'children'), 'routes'));
    }

    public function testParameterOverriding()
    {
        $factory = new GenericFactory();
        $route = $factory($this->getContainer('/bar', null), '', [
            '/foo',
            ['foo' => 'bar', 'baz' => 'bat'],
            ['POST'],
            'path' => '/bar',
            'defaults' => ['foo' => 'baz', 'bat' => 'bar'],
            'methods' => ['PUT'],
        ]);

        $this->assertInstanceOf(Generic::class, $route);
        $this->assertAttributeSame(['foo' => 'baz', 'bat' => 'bar', 'baz' => 'bat'], 'defaults', $route);
        $this->assertAttributeSame(['POST', 'PUT'], 'methods', $route);
    }

    public function testFactoryWithSpecifiedParsers()
    {
        $pathParser     = $this->prophesize(ParserInterface::class)->reveal();
        $hostnameParser = $this->prophesize(ParserInterface::class)->reveal();

        $factory = new GenericFactory();
        $route = $factory($this->getContainer(null, null, $pathParser, $hostnameParser), '', [
            'path_parser' => 'CustomPath',
            'hostname_parser' => 'CustomHostname',
        ]);

        $this->assertInstanceOf(Generic::class, $route);
        $this->assertAttributeSame($pathParser, 'pathParser', $route);
        $this->assertAttributeSame($hostnameParser, 'hostnameParser', $route);
    }

    /**
     * @param  string          $pathPattern
     * @param  string          $hostnamePattern
     * @param  ParserInterface $pathParser
     * @param  ParserInterface $hostnameParser
     * @return ContainerInterface
     */
    protected function getContainer(
        $pathPattern,
        $hostnamePattern,
        ParserInterface $pathParser = null,
        ParserInterface $hostnameParser = null
    ) {
        $parserManager = $this->prophesize(ParserManager::class);
        $parserManager->build('PathSegment', Argument::withEntry('path', $pathPattern))
            ->willReturn($this->prophesize(ParserInterface::class)->reveal());
        $parserManager->build('HostnameSegment', Argument::withEntry('hostname', $hostnamePattern))
            ->willReturn($this->prophesize(ParserInterface::class)->reveal());

        if (null !== $pathParser) {
            $parserManager->build('CustomPath', Argument::type('array'))->willReturn($pathParser);
        }

        if (null !== $hostnameParser) {
            $parserManager->build('CustomHostname', Argument::type('array'))->willReturn($hostnameParser);
        }

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RouteManager::class)->willReturn($this->prophesize(RouteManager::class)->reveal());
        $container->get(ParserManager::class)->willReturn($parserManager->reveal());

        return $container->reveal();
    }
}
