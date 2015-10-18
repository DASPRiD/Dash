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
use Dash\Parser\Segment;
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
        $parser  = $factory($this->prophesize(ContainerInterface::class)->reveal(), '');

        $this->assertInstanceOf(Segment::class, $parser);
        $this->assertAttributeSame('/', 'delimiter', $parser);
        $this->assertAttributeSame('', 'pattern', $parser);
        $this->assertAttributeSame([], 'constraints', $parser);
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new PathSegmentFactory();
        $parser = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
                'path'        => '/:foo/bar',
                'constraints' => ['foo' => '1'],
            ]
        );

        $this->assertInstanceOf(Segment::class, $parser);
        $this->assertAttributeSame('/', 'delimiter', $parser);
        $this->assertAttributeSame('/:foo/bar', 'pattern', $parser);
        $this->assertAttributeSame(['foo' => '1'], 'constraints', $parser);
    }

    public function testFactoryReusesInstancesWithSameConfiguration()
    {
        $factory = new PathSegmentFactory();
        $parser1 = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
            'path'        => '/:foo/bar',
            'constraints' => ['foo' => '1'],
            ]
        );
        $parser2 = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
            'path'        => '/:foo/bar',
            'constraints' => ['foo' => '1'],
            ]
        );
        $parser3 = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
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
