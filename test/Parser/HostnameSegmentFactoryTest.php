<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Parser\HostnameSegmentFactory;
use Dash\Parser\Segment;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\HostnameSegmentFactory
 */
class HostnameSegmentFactoryTest extends TestCase
{
    public function testFactoryWithoutConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $parser  = $factory($this->prophesize(ContainerInterface::class)->reveal(), '');

        $this->assertInstanceOf(Segment::class, $parser);
        $this->assertAttributeSame('.', 'delimiter', $parser);
        $this->assertAttributeSame('', 'pattern', $parser);
        $this->assertAttributeSame([], 'constraints', $parser);
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $parser = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
                'hostname'    => ':foo.example.com',
                'constraints' => ['foo' => '1'],
            ]
        );

        $this->assertInstanceOf(Segment::class, $parser);
        $this->assertAttributeSame('.', 'delimiter', $parser);
        $this->assertAttributeSame(':foo.example.com', 'pattern', $parser);
        $this->assertAttributeSame(['foo' => '1'], 'constraints', $parser);
    }

    public function testFactoryReusesInstancesWithSameConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $parser1 = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
                'hostname'    => ':foo.example.com',
                'constraints' => ['foo' => '1'],
            ]
        );
        $parser2 = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
                'hostname'    => ':foo.example.com',
                'constraints' => ['foo' => '1'],
            ]
        );
        $parser3 = $factory(
            $this->prophesize(ContainerInterface::class)->reveal(),
            '',
            [
                'hostname'    => ':bar.example.com',
                'constraints' => ['bar' => '1'],
            ]
        );

        $this->assertSame($parser1, $parser2);
        $this->assertNotSame($parser1, $parser3);
    }
}
