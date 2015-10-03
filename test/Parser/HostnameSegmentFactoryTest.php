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
        $parser  = $factory($this->getMock(ContainerInterface::class), '');

        $parseResult = $parser->parse('', 0);
        $this->assertEquals([], $parseResult->getParams());
        $this->assertEquals(0, $parseResult->getMatchLength());
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $parser = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
                'hostname'    => ':foo.example.com',
                'constraints' => ['foo' => '1'],
            ]
        );

        $this->assertNull($parser->parse('0.example.com', 0));
        $parseResult = $parser->parse('1.example.com', 0);
        $this->assertEquals(['foo' => '1'], $parseResult->getParams());
        $this->assertEquals(13, $parseResult->getMatchLength());
    }

    public function testFactoryReusesInstancesWithSameConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $parser1 = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
                'hostname'    => ':foo.example.com',
                'constraints' => ['foo' => '1'],
            ]
        );
        $parser2 = $factory(
            $this->getMock(ContainerInterface::class),
            '',
            [
                'hostname'    => ':foo.example.com',
                'constraints' => ['foo' => '1'],
            ]
        );
        $parser3 = $factory(
            $this->getMock(ContainerInterface::class),
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
