<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\Parser;

use Dash\Router\Http\Parser\HostnameSegmentFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\Parser\HostnameSegmentFactory
 */
class HostnameSegmentFactoryTest extends TestCase
{
    public function testFactoryWithoutConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $parser  = $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));

        $parseResult = $parser->parse('', 0);
        $this->assertEquals([], $parseResult->getParams());
        $this->assertEquals(0, $parseResult->getMatchLength());
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $factory->setCreationOptions([
            'hostname'    => ':foo.example.com',
            'constraints' => ['foo' => '1']
        ]);
        $parser = $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));

        $this->assertNull($parser->parse('0.example.com', 0));
        $parseResult = $parser->parse('1.example.com', 0);
        $this->assertEquals(['foo' => '1'], $parseResult->getParams());
        $this->assertEquals(13, $parseResult->getMatchLength());
    }

    public function testFactoryReusesInstancesWithSameConfiguration()
    {
        $factory = new HostnameSegmentFactory();
        $options = [
            'hostname'    => ':foo.example.com',
            'constraints' => ['foo' => '1']
        ];

        $factory->setCreationOptions($options);
        $parser = $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));

        $factory->setCreationOptions($options);
        $this->assertSame($parser, $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface')));

        $factory->setCreationOptions([]);
        $this->assertNotSame($parser, $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface')));
    }
}
