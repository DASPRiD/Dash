<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Parser\PathSegmentFactory;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\PathSegmentFactory
 */
class PathSegmentFactoryTest extends TestCase
{
    public function testFactoryWithoutConfiguration()
    {
        $factory = new PathSegmentFactory();
        $parser  = $factory($this->getMock(ContainerInterface::class), '');

        $parseResult = $parser->parse('', 0);
        $this->assertEquals([], $parseResult->getParams());
        $this->assertEquals(0, $parseResult->getMatchLength());
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new PathSegmentFactory();
        $parser = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
            'path'        => '/:foo/bar',
            'constraints' => ['foo' => '1'],
            ]
        );

        $this->assertNull($parser->parse('/0/bar', 0));
        $parseResult = $parser->parse('/1/bar', 0);
        $this->assertEquals(['foo' => '1'], $parseResult->getParams());
        $this->assertEquals(6, $parseResult->getMatchLength());
    }

    public function testFactoryReusesInstancesWithSameConfiguration()
    {
        $factory = new PathSegmentFactory();
        $parser1 = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
            'path'        => '/:foo/bar',
            'constraints' => ['foo' => '1'],
            ]
        );
        $parser2 = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
            'path'        => '/:foo/bar',
            'constraints' => ['foo' => '1'],
            ]
        );
        $parser3 = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
            'path'        => '/:bar/bar',
            'constraints' => ['bar' => '1'],
            ]
        );

        $this->assertSame($parser1, $parser2);
        $this->assertNotSame($parser1, $parser3);
    }
}
