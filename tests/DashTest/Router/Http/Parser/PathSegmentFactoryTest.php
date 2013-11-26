<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\Parser;

use Dash\Router\Http\Parser\PathSegmentFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\Parser\PathSegmentFactory
 */
class PathSegmentFactoryTest extends TestCase
{
    public function testFactoryWithoutConfiguration()
    {
        $factory = new PathSegmentFactory();
        $parser  = $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));

        $parseResult = $parser->parse('', 0);
        $this->assertEquals([], $parseResult->getParams());
        $this->assertEquals(0, $parseResult->getMatchLength());
    }

    public function testFactoryWithConfiguration()
    {
        $factory = new PathSegmentFactory();
        $factory->setCreationOptions([
            'path'        => '/:foo/bar',
            'constraints' => ['foo' => '1']
        ]);
        $parser = $factory->createService($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));

        $this->assertNull($parser->parse('/0/bar', 0));
        $parseResult = $parser->parse('/1/bar', 0);
        $this->assertEquals(['foo' => '1'], $parseResult->getParams());
        $this->assertEquals(6, $parseResult->getMatchLength());
    }

    public function testFactoryReusesInstancesWithSameConfiguration()
    {
        $factory = new PathSegmentFactory();
        $options = [
            'path'        => '/:foo/bar',
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
